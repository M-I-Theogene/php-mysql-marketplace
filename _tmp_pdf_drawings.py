"""List the gold vector drawings that actually reached the PDF."""

import os
import sys

OUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "pawapay-documents")
NAME = "Cypadi Ltd - KYC and AML Policy - CYP-AML-001 v1.1.pdf"
GOLD = (0.847, 0.659, 0.188)
TOLERANCE = 0.12


def near_gold(color):
    if not color:
        return False
    for channel, target in zip(color, GOLD):
        try:
            if abs(float(channel) - target) >= TOLERANCE:
                return False
        except (TypeError, ValueError):
            return False
    return True


def collect_gold(page):
    gold = []
    for drawing in page.get_drawings():
        if near_gold(drawing.get("color")) or near_gold(drawing.get("fill")):
            gold.append(drawing)
    return gold


def report(index, drawings, gold):
    print("== page %d drawings=%d gold=%d" % (index + 1, len(drawings), len(gold)))
    for drawing in gold[:10]:
        rect = [round(value, 1) for value in drawing.get("rect")]
        print("   type=%s stroke=%s fill=%s rect=%s" % (
            drawing.get("type"), drawing.get("color"), drawing.get("fill"), rect))


def main():
    import fitz

    document = fitz.open(os.path.join(OUT_DIR, NAME))
    for index in range(min(2, document.page_count)):
        page = document[index]
        drawings = page.get_drawings()
        report(index, drawings, collect_gold(page))
    document.close()
    return 0


if __name__ == "__main__":
    sys.exit(main())
