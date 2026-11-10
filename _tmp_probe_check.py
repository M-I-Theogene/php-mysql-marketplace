"""Report, for the probe PDF, where gold rules appear.

Each variant in the probe is preceded by a label paragraph, so the vertical
positions of gold bands identify which variant Word rendered.
"""

import os

import fitz

ROOT = os.path.dirname(os.path.abspath(__file__))
SOURCE = os.path.join(ROOT, "_tmp_probe_border.pdf")
GOLD = (0xD8, 0xA8, 0x30)
TOLERANCE = 30
SCALE = 2.0


def is_gold(pixel):
    return all(abs(pixel[index] - GOLD[index]) <= TOLERANCE for index in range(3))


def main():
    document = fitz.open(SOURCE)
    page = document[0]
    pixmap = page.get_pixmap(matrix=fitz.Matrix(SCALE, SCALE))
    samples = pixmap.samples
    width, height, channels = pixmap.width, pixmap.height, pixmap.n
    bands = []
    for y in range(height):
        offset = y * pixmap.stride
        spans = 0
        for x in range(width):
            base = offset + x * channels
            if is_gold((samples[base], samples[base + 1], samples[base + 2])):
                spans += 1
        if spans > width * 0.3:
            if bands and y - bands[-1][1] <= 3:
                bands[-1] = (bands[-1][0], y, max(bands[-1][2], spans))
            else:
                bands.append((y, y, spans))

    print("gold bands: %d" % len(bands))
    for index, band in enumerate(bands, 1):
        top = band[0] / SCALE
        print("  band %d: y=%.1f-%.1f px=%d" % (index, top, band[1] / SCALE, band[2]))

    for label in ("VARIANT A", "VARIANT B", "VARIANT C", "VARIANT D"):
        hits = page.search_for(label)
        if hits:
            print("%s label at y=%.1f" % (label, hits[0].y0))
        else:
            print("%s not found" % label)
    document.close()


if __name__ == "__main__":
    main()
