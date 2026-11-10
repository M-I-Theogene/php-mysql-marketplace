"""Count gold rule rectangles in every generated PDF."""

import glob
import os

import fitz

OUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "pawapay-documents")
GOLD = (0.847, 0.659, 0.188)


def is_gold(color):
    if not color:
        return False
    return all(abs(float(channel) - target) < 0.1 for channel, target in zip(color, GOLD))


def main():
    for path in sorted(glob.glob(os.path.join(OUT_DIR, "*.pdf"))):
        if "CYPADI Ltd" in path:
            continue
        document = fitz.open(path)
        total = 0
        details = []
        for index, page in enumerate(document):
            hits = [d for d in page.get_drawings()
                    if is_gold(d.get("fill")) or is_gold(d.get("color"))]
            total += len(hits)
            if hits:
                details.append("p%d:%d" % (index + 1, len(hits)))
        print("%-62s pages=%d gold=%d %s" % (
            os.path.basename(path)[:62], document.page_count, total, " ".join(details)))
        document.close()


if __name__ == "__main__":
    main()
