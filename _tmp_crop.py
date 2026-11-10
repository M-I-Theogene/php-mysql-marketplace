"""Crop and magnify a region of a rendered PDF page for close inspection."""

import os
import sys

OUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "pawapay-documents")
PREVIEW_DIR = os.path.join(OUT_DIR, "_preview")


def main(name, top, bottom, zoom=3):
    import fitz

    path = os.path.join(OUT_DIR, name)
    document = fitz.open(path)
    page = document[0]
    clip = fitz.Rect(0, top, page.rect.width, bottom)
    pixmap = page.get_pixmap(dpi=150, clip=clip)
    target = os.path.join(PREVIEW_DIR, "crop.png")
    pixmap.save(target)
    print("saved %s %dx%d" % (target, pixmap.width, pixmap.height))
    document.close()


if __name__ == "__main__":
    main(sys.argv[1], float(sys.argv[2]), float(sys.argv[3]))
