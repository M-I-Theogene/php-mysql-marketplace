"""Count gold rule pixels per page of the generated PDFs.

The documents use a gold accent (D8A830) for horizontal rules. If Word dropped
the paragraph borders, no gold pixel will be found on the page.
"""

import glob
import os
import sys

OUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "pawapay-documents")
GOLD = (0xD8, 0xA8, 0x30)
TOLERANCE = 60


def is_gold(pixel):
    return (
        abs(pixel[0] - GOLD[0]) < TOLERANCE
        and abs(pixel[1] - GOLD[1]) < TOLERANCE
        and abs(pixel[2] - GOLD[2]) < TOLERANCE
        and pixel[0] > pixel[2] + 60
    )


def scan(path):
    import fitz

    document = fitz.open(path)
    print("== %s (%d pages)" % (os.path.basename(path), document.page_count))
    for index, page in enumerate(document):
        pixmap = page.get_pixmap(dpi=150)
        samples = pixmap.samples
        stride = pixmap.n
        rows = set()
        total = 0
        for y in range(pixmap.height):
            offset = y * pixmap.stride
            for x in range(pixmap.width):
                base = offset + x * stride
                if is_gold(samples[base:base + 3]):
                    total += 1
                    rows.add(y)
        if total:
            groups = []
            ordered = sorted(rows)
            start = previous = ordered[0]
            for value in ordered[1:]:
                if value - previous > 3:
                    groups.append((start, previous))
                    start = value
                previous = value
            groups.append((start, previous))
            print("   p%-2d gold_px=%-6d rule_bands=%d %s" % (
                index + 1, total, len(groups), groups[:8]))
        else:
            print("   p%-2d gold_px=0" % (index + 1))
    document.close()


def main():
    for path in sorted(glob.glob(os.path.join(OUT_DIR, "*.pdf"))):
        if "CYPADI Ltd.pdf" in path:
            continue
        scan(path)
    return 0


if __name__ == "__main__":
    sys.exit(main())
