"""Dump text lines of the generated documents that match a search term."""

import glob
import os
import sys

import docx

OUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "pawapay-documents")


def iter_lines(document):
    for paragraph in document.paragraphs:
        if paragraph.text.strip():
            yield paragraph.text.strip()
    for table in document.tables:
        for row in table.rows:
            cells = [cell.text.strip() for cell in row.cells]
            line = " | ".join(cell for cell in cells if cell)
            if line:
                yield line


def main(needle):
    for path in sorted(glob.glob(os.path.join(OUT_DIR, "*.docx"))):
        document = docx.Document(path)
        print("=== %s" % os.path.basename(path))
        for line in iter_lines(document):
            if needle.lower() in line.lower():
                print("   %s" % line)


if __name__ == "__main__":
    main(sys.argv[1] if len(sys.argv) > 1 else "")
