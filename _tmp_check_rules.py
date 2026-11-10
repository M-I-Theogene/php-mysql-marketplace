"""Check that the gold rules and table shading actually reach the PDF.

Word silently drops w:pBdr when the paragraph-property child order is wrong,
so this scans the PDF's drawing operators for gold strokes (#D8A830) and for
filled rectangles, and reports what it finds per page.
"""

import os
from collections import Counter

import fitz

OUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "pawapay-documents")
GOLD = (0xD8, 0xA8, 0x30)


def normalise(color):
    if color is None:
        return None
    if isinstance(color, (int, float)):
        color = (color,)
    if len(color) == 1:
        color = (color[0], color[0], color[0])
    if len(color) < 3:
        return None
    return tuple(round(component, 3) for component in color[:3])


def is_gold(color, tolerance=0.08):
    if color is None:
        return False
    reference = tuple(component / 255.0 for component in GOLD)
    return all(abs(color[index] - reference[index]) <= tolerance for index in range(3))


def main():
    for name in sorted(os.listdir(OUT_DIR)):
        if not name.lower().endswith(".pdf"):
            continue
        tag = "KYC" if "KYC" in name else "VDD"
        document = fitz.open(os.path.join(OUT_DIR, name))
        print("=== %s (%d pages) ===" % (tag, document.page_count))
        for index, page in enumerate(document):
            strokes = Counter()
            fills = Counter()
            rules = []
            for drawing in page.get_drawings():
                stroke = normalise(drawing.get("color"))
                fill = normalise(drawing.get("fill"))
                if stroke is not None:
                    strokes[stroke] += 1
                if fill is not None:
                    fills[fill] += 1
                rect = drawing.get("rect")
                if rect is None:
                    continue
                color = stroke if stroke is not None else fill
                if color is None:
                    continue
                if rect.width > 100 and rect.height < 4:
                    rules.append((round(rect.y0, 1), round(rect.height, 2), color))
            gold_fills = sum(
                count for color, count in fills.items() if is_gold(color)
            )
            print(
                "  page %d: gold_strokes=%d gold_fills=%d top_fills=%s"
                % (index + 1, sum(count for c, count in strokes.items() if is_gold(c)),
                   gold_fills, dict(fills.most_common(3)))
            )
            if rules:
                for rule in rules[:8]:
                    print("          rule y=%.1f h=%.2f color=%s" % rule)
            else:
                print("          rule none")
        document.close()


if __name__ == "__main__":
    main()
