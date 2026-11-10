"""Render the built PDFs to PNG so the pages can be inspected visually."""

import os
import sys

OUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "pawapay-documents")
PREVIEW_DIR = os.path.join(OUT_DIR, "_preview")

def main():
    try:
        import fitz
    except ImportError:
        print("PYMUPDF_MISSING")
        return 2

    if not os.path.isdir(PREVIEW_DIR):
        os.makedirs(PREVIEW_DIR)

    for name in sorted(os.listdir(OUT_DIR)):
        if not name.lower().endswith(".pdf"):
            continue
        document = fitz.open(os.path.join(OUT_DIR, name))
        tag = "kyc" if "KYC" in name else "vdd"
        print("%s pages=%d" % (tag, document.page_count))
        for index, page in enumerate(document):
            pixmap = page.get_pixmap(dpi=110)
            target = os.path.join(PREVIEW_DIR, "%s-p%d.png" % (tag, index + 1))
            pixmap.save(target)
            print("   %s %dx%d" % (os.path.basename(target), pixmap.width, pixmap.height))
        document.close()
    return 0


if __name__ == "__main__":
    sys.exit(main())
