"""List the [[PLACEHOLDER]] tokens still present in the two built documents.

Placeholders are printed in red in the documents, so they must be replaced
before the files are printed, signed and scanned back to PDF.
"""

import glob
import os
import re

import docx

PATTERN = re.compile(r"\[\[(.+?)\]\]")
OUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "pawapay-documents")


def collect(path):
    document = docx.Document(path)
    chunks = [paragraph.text for paragraph in document.paragraphs]
    for table in document.tables:
        for row in table.rows:
            for cell in row.cells:
                chunks.append(cell.text)
    return sorted(set(PATTERN.findall("\n".join(chunks))))


def main():
    for path in sorted(glob.glob(os.path.join(OUT_DIR, "*.docx"))):
        print(os.path.basename(path))
        for name in collect(path):
            print("   [[%s]]" % name)


if __name__ == "__main__":
    main()
