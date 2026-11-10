"""Dump the w:pBdr XML that the builder wrote into the generated documents."""

import os
import re
import sys
import zipfile

DOCX = os.path.join(
    os.path.dirname(os.path.abspath(__file__)),
    "pawapay-documents",
    "Cypadi Ltd - KYC and AML Policy - CYP-AML-001 v1.1.docx",
)


def dump(entry, xml, limit=6):
    print("== %s" % entry)
    blocks = re.findall(r"<w:p\b.*?</w:p>", xml, re.S)
    shown = 0
    for block in blocks:
        if "<w:pBdr>" not in block:
            continue
        shown += 1
        if shown > limit:
            break
        print("   %s" % block[:600])
    print("   paragraphs with pBdr: %d" % len([b for b in blocks if "<w:pBdr>" in b]))


def main():
    with zipfile.ZipFile(DOCX) as archive:
        dump("word/document.xml", archive.read("word/document.xml").decode("utf-8", "ignore"))
        for name in archive.namelist():
            if re.match(r"word/header\d+\.xml$", name):
                dump(name, archive.read(name).decode("utf-8", "ignore"), limit=2)
    return 0


if __name__ == "__main__":
    sys.exit(main())
