# -*- coding: utf-8 -*-
"""One-off: extract text/tables from the budget disbursement docs in docs/documents/.
Writes a UTF-8 dump so we can read Thai content cheaply instead of rendering each PDF.
"""
import os
import glob
import sys

import pdfplumber

DOCS = "docs/documents"
SUBDIR = os.path.join(DOCS, "สะสม")
OUT = "python/extract_docs_output.txt"


def th_year_sort_key(path):
    # Best-effort: keep filesystem order; real sort happens by reading dates later.
    return os.path.basename(path)


def dump_pdf(fh, path):
    name = os.path.basename(path)
    fh.write("\n" + "=" * 80 + "\n")
    fh.write(f"FILE: {name}\n")
    fh.write("=" * 80 + "\n")
    try:
        with pdfplumber.open(path) as pdf:
            fh.write(f"[pages: {len(pdf.pages)}]\n")
            for pi, page in enumerate(pdf.pages):
                fh.write(f"\n--- page {pi+1} (text) ---\n")
                txt = page.extract_text() or "(no extractable text)"
                fh.write(txt + "\n")
                tables = page.extract_tables()
                for ti, table in enumerate(tables):
                    fh.write(f"\n--- page {pi+1} table {ti+1} ({len(table)} rows) ---\n")
                    for row in table:
                        cells = ["" if c is None else str(c).replace("\n", " ") for c in row]
                        fh.write(" | ".join(cells) + "\n")
    except Exception as e:
        fh.write(f"[ERROR reading {name}: {e}]\n")


def main():
    pdfs = sorted(glob.glob(os.path.join(SUBDIR, "*.pdf")), key=th_year_sort_key)
    top_pdfs = sorted(glob.glob(os.path.join(DOCS, "*.pdf")), key=th_year_sort_key)
    all_pdfs = top_pdfs + pdfs
    with open(OUT, "w", encoding="utf-8") as fh:
        fh.write(f"PDF count: {len(all_pdfs)}\n")
        for p in all_pdfs:
            dump_pdf(fh, p)
    print(f"Wrote {OUT}; {len(all_pdfs)} PDFs processed")
    print(f"Output size: {os.path.getsize(OUT)} bytes")


if __name__ == "__main__":
    main()
