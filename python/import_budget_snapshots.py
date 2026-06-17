# -*- coding: utf-8 -*-
"""Phase 3 importer: extract per-organization cumulative budget snapshots from the
budget-execution PDFs and emit idempotent UPSERT SQL into budget_monthly_snapshots.

Each PDF = one snapshot date; each page = one unit (กอง/ศูนย์/...). We read the
"รวมงบประมาณทั้งสิ้น" grand-total line per page, map the unit name to an
organization code, and write an INSERT ... ON DUPLICATE KEY UPDATE (org-level row).

Uses pypdf + multiprocessing (extraction is CPU-bound) to process all PDFs in a
few minutes. Output: python/import_budget_snapshots.sql + a console summary.
No Python DB driver required (org id resolved by code in SQL).
"""
import glob
import os
import re
import sys
import unicodedata
from multiprocessing import Pool, cpu_count

from pypdf import PdfReader

DOCS = "docs/documents/สะสม"
OUT_SQL = "python/import_budget_snapshots.sql"

TITLE_RE = re.compile(r"รายละเอียดงบประมาณที่ได้รับจัดสรร")
TOTAL_RE = re.compile(r"^รวมงบประมาณทั้งสิ้น")
DATE_RE = re.compile(r"วันที่\s*(\d{1,2})\s*([ก-ฮ\.]+?)\s*(\d{2})")

TH_MONTH = {
    "ม.ค.": 1, "ก.พ.": 2, "มี.ค.": 3, "เม.ย.": 4, "พ.ค.": 5, "มิ.ย.": 6,
    "ก.ค.": 7, "ส.ค.": 8, "ก.ย.": 9, "ต.ค.": 10, "พ.ย.": 11, "ธ.ค.": 12,
}

UNIT_CODE = {
    "กองยุทธศาสตร์และแผนงาน": "OPS-STRAT",
    "กองยุทธศาสตร์และแผนงาน (บริหารส่วนกลาง)": "OPS-STRAT-CENTRAL",
    "กองยุทธศาสตร์และแผนงาน ส่วนนโยบายและยุทธศาสตร์จังหวัดชายแดนภาคใต้": "OPS-STRAT-SBPAC",
    "กองประสานราชการยุติธรรมจังหวัด": "OPS-PROV",
    "สำนักงานส่งเสริมสัมมาชีพและผลิตภัณฑ์เพื่อการพัฒนาพฤตินิสัย": "OPS-VOC",
    "ศูนย์บริการร่วม กระทรวงยุติธรรม": "OPS-SC",
    "กองการต่างประเทศ": "OPS-INTL",
    "สถาบันพัฒนาบุคลากรกระทรวงยุติธรรม": "OPS-HRD",
    "กองกฎหมาย": "OPS-LAW",
    "กองกลาง": "OPS-CENTRAL",
    "สำนักงานรัฐมนตรี": "OPS-MIN",
    "กลุ่มตรวจสอบภายใน": "OPS-AUDIT",
    "สำนักผู้ตรวจราชการกระทรวงยุติธรรม": "OPS-INSP",
    "กองบริหารทรัพยากรบุคคล": "OPS-HR",
    "กองออกแบบและก่อสร้าง": "OPS-CONS",
    "ศูนย์เทคโนโลยีสารสนเทศและการสื่อสาร": "OPS-ICT",
    "กองบริหารการคลัง": "OPS-FIN",
    "กองบริหารการคลัง (ค่าใช้จ่ายส่วนกลาง)": "OPS-FIN-CENTRAL",
    "กลุ่มพัฒนาระบบบริหาร กระทรวงยุติธรรม": "OPS-PSDG",
    "ศูนย์ปฏิบัติการต่อต้านการทุจริต กระทรวงยุติธรรม": "OPS-ACT",
    "กองพัฒนานวัตกรรมการยุติธรรม": "OPS-INNO",
    "กลุ่มภารกิจพัฒนาพฤตินิสัย": "OPS-REHAB",
    "เนติบัณฑิตยสภา/สภาทนายความ": "MOJ-EXT-LAW",
}


def norm(s):
    s = s.replace("ํา", "ำ")
    s = unicodedata.normalize("NFC", s)
    return re.sub(r"\s+", " ", s).strip()


def parse_date(filename):
    m = DATE_RE.search(norm(filename))
    if not m:
        return None
    day = int(m.group(1))
    mon = TH_MONTH.get(m.group(2).strip())
    if not mon:
        return None
    be_year = 2500 + int(m.group(3))
    ce_year = be_year - 543
    fy = be_year + 1 if mon >= 10 else be_year
    return f"{ce_year:04d}-{mon:02d}-{day:02d}", fy


def _num(tok):
    tok = tok.strip()
    if tok in ("-", "", "–"):
        return 0.0
    try:
        return float(tok.replace(",", ""))
    except ValueError:
        return None


def parse_total_line(line):
    rest = TOTAL_RE.sub("", line).strip()
    vals = [_num(t) for t in rest.split()]
    if len(vals) < 9 or any(vals[i] is None for i in (0, 1, 4, 6, 8)):
        return None
    return {
        "allocated_pba": vals[0], "allocated_received": vals[1],
        "transfer": vals[2] or 0.0, "disbursed": vals[4],
        "pending": vals[5] or 0.0, "po_commitment": vals[6], "remaining": vals[8],
    }


def process_file(path):
    """Worker: -> (rows, unmatched_names, basename). rows = list of (code, fy, date, total)."""
    base = os.path.basename(path)
    dt = parse_date(base)
    if not dt:
        return [], [], base
    snap_date, fy = dt
    rows, unmatched = [], []
    try:
        reader = PdfReader(path)
        for page in reader.pages:
            txt = page.extract_text() or ""
            lines = [l.strip() for l in txt.split("\n") if l.strip()]
            unit = None
            for i, l in enumerate(lines):
                if TITLE_RE.search(l) and i + 1 < len(lines):
                    unit = norm(lines[i + 1])
                    break
            if not unit:
                continue
            total = next((parse_total_line(l) for l in lines if TOTAL_RE.match(l)), None)
            if total is None:
                continue
            code = UNIT_CODE.get(unit)
            if not code:
                unmatched.append(unit)
                continue
            rows.append((code, fy, snap_date, total))
    except Exception as e:  # noqa: BLE001
        print(f"ERROR {base}: {e}", flush=True)
    print(f"  done {base}: {len(rows)} rows", flush=True)
    return rows, unmatched, base


def main():
    pdfs = sorted(glob.glob(os.path.join(DOCS, "*.pdf")))
    print(f"Extracting {len(pdfs)} PDFs on {min(8, cpu_count())} workers...", flush=True)
    with Pool(processes=min(8, cpu_count())) as pool:
        results = pool.map(process_file, pdfs)

    all_rows, unmatched = [], {}
    for rows, um, _ in results:
        all_rows.extend(rows)
        for u in um:
            unmatched[u] = unmatched.get(u, 0) + 1

    # Aggregate by (code, fiscal_year, date): a unit spanning several pages has
    # one "รวมงบประมาณทั้งสิ้น" per budget section, so the unit total = their sum.
    fields = ("allocated_pba", "allocated_received", "transfer", "disbursed",
              "pending", "po_commitment", "remaining")
    agg = {}
    for code, fy, snap_date, t in all_rows:
        key = (code, fy, snap_date)
        if key not in agg:
            agg[key] = {f: 0.0 for f in fields}
        for f in fields:
            agg[key][f] += t[f]

    with open(OUT_SQL, "w", encoding="utf-8") as fh:
        fh.write("SET NAMES utf8mb4;\n")
        for (code, fy, snap_date), t in sorted(agg.items()):
            fh.write(
                "INSERT INTO budget_monthly_snapshots "
                "(organization_id, fiscal_year, snapshot_date, allocated_pba, allocated_received, "
                "transfer, disbursed, pending, po_commitment, remaining, source)\n"
                f"SELECT id, {fy}, '{snap_date}', {t['allocated_pba']:.2f}, {t['allocated_received']:.2f}, "
                f"{t['transfer']:.2f}, {t['disbursed']:.2f}, {t['pending']:.2f}, {t['po_commitment']:.2f}, "
                f"{t['remaining']:.2f}, 'pdf_import'\n"
                f"FROM organizations WHERE code='{code}'\n"
                "ON DUPLICATE KEY UPDATE allocated_pba=VALUES(allocated_pba), "
                "allocated_received=VALUES(allocated_received), transfer=VALUES(transfer), "
                "disbursed=VALUES(disbursed), pending=VALUES(pending), "
                "po_commitment=VALUES(po_commitment), remaining=VALUES(remaining), source='pdf_import';\n"
            )

    print(f"\nPDFs: {len(pdfs)} | page-rows: {len(all_rows)} | org-date snapshots: {len(agg)} | SQL -> {OUT_SQL}", flush=True)
    if unmatched:
        print("UNMATCHED units:")
        for u, c in sorted(unmatched.items(), key=lambda x: -x[1]):
            print(f"  [{c}] {u}")
    else:
        print("All units matched.")


if __name__ == "__main__":
    sys.stdout.reconfigure(encoding="utf-8")
    main()
