"""Census of drawing colours on the first pages of the generated PDF."""

import collections
import os
import sys

OUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "pawapay-documents")
NAME = "Cypadi Ltd - KYC and AML Policy - CYP-AML-001 v1.1.pdf"


def key(color):
    if not color:
        return "none"
    return "#" + "".join("%02X" % round(float(channel) * 255) for channel in color)


def main():
    import fitz

    document = fitz.open(os.path.join(OUT_DIR, NAME))
    for index in range(min(2, document.page_count)):
        page = document[index]
        strokes = collections.Counter()
        fills = collections.Counter()
        for drawing in page.get_drawings():
            strokes[key(drawing.get("color"))] += 1
            fills[key(drawing.get("fill"))] += 1
        print("== page %d" % (index + 1))
        print("   stroke: %s" % dict(strokes.most_common(8)))
        print("   fill:   %s" % dict(fills.most_common(8)))
    document.close()
    return 0


if __name__ == "__main__":
    sys.exit(main())
