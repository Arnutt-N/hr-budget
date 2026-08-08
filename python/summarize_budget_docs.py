# -*- coding: utf-8 -*-
"""Compact index of the budget docs: per PDF -> date, page count, unit headers,
and each unit's "รวมงบประมาณทั้งสิ้น" grand-total line. Plus the .xls summary sheet.
Output is small and UTF-8 so it can be read cheaply.
"""
import os
import glob
import re

import pdfplumber
import pandas as pd

DOCS = "docs/documents"
SUBDIR = os.path.join(DOCS, "สะสม")
OUT = "python/summary_budget_docs.txt"

TITLE_RE = re.compile(r"รายละเอียดงบประมาณที่ได้รับจัดสรร")
TOTAL_RE = re.compile(r"รวมงบประมาณทั้งสิ้น")


def summarize_pdf(path):
    name = os.path.basename(path)
    lines = [f"\n### {name}"]
    try:
        with pdfplumber.open(path) as pdf:
            lines.append(f"pages={len(pdf.pages)}")
            for pi, page in enumerate(pdf.pages):
                txt = page.extract_text() or ""
                rows = [r.strip() for r in txt.split("\n") if r.strip()]
                # unit header = line right after the title line
                unit = None
                total = None
                for i, r in enumerate(rows):
                    if TITLE_RE.search(r) and i + 1 < len(rows):
                        unit = rows[i + 1]
                    if TOTAL_RE.search(r) and total is None:
                        total = r
                if unit or total:
                    lines.append(f"  p{pi+1}: {unit or '(no unit)'}")
                    if total:
                        lines.append(f"        TOTAL: {total}")
    except Exception as e:
        lines.append(f"  [ERROR: {e}]")
    return "\n".join(lines)


def dump_xls(fh):
    f = os.path.join(DOCS, "ผลการเบิกจ่าย วันที่ 30 ธ.ค.68.xls")
    fh.write("\n" + "=" * 70 + "\nXLS: " + os.path.basename(f) + "\n" + "=" * 70 + "\n")
    xls = pd.ExcelFile(f, engine="xlrd")
    for sh in xls.sheet_names:
        df = pd.read_excel(f, sheet_name=sh, engine="xlrd", header=None)
        fh.write(f"\n--- sheet '{sh}' shape={df.shape} ---\n")
        # print non-empty rows compactly
        for _, row in df.iterrows():
            cells = [str(c) for c in row.tolist() if pd.notna(c) and str(c).strip() != ""]
            if cells:
                fh.write(" | ".join(cells) + "\n")


def main():
    pdfs = sorted(glob.glob(os.path.join(DOCS, "*.pdf"))) + sorted(glob.glob(os.path.join(SUBDIR, "*.pdf")))
    with open(OUT, "w", encoding="utf-8") as fh:
        fh.write(f"PDF files: {len(pdfs)}\n")
        for p in pdfs:
            fh.write(summarize_pdf(p) + "\n")
        dump_xls(fh)
    print(f"Wrote {OUT} ({os.path.getsize(OUT)} bytes), {len(pdfs)} PDFs")


if __name__ == "__main__":
    main()
