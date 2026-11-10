"""Count gold pixels in the rendered page previews.

The PDF drawing scan cannot see content that Word draws inside form XObjects,
so this inspects the rendered PNGs directly: gold pixels (#D8A830) are counted
per page and their vertical positions reported.
"""

import os

from PIL import Image

PREVIEW_DIR = os.path.join(
    os.path.dirname(os.path.abspath(__file__)), "pawapay-documents", "_preview"
)
GOLD = (0xD8, 0xA8, 0x30)
TOLERANCE = 26


def is_gold(pixel):
    red, green, blue = pixel[:3]
    return (
        abs(red - GOLD[0]) <= TOLERANCE
        and abs(green - GOLD[1]) <= TOLERANCE
        and abs(blue - GOLD[2]) <= TOLERANCE
    )


def main():
    for name in sorted(os.listdir(PREVIEW_DIR)):
        if not name.lower().endswith(".png"):
            continue
        image = Image.open(os.path.join(PREVIEW_DIR, name)).convert("RGB")
        width, height = image.size
        pixels = image.load()
        rows = {}
        total = 0
        for y in range(height):
            count = 0
            for x in range(width):
                if is_gold(pixels[x, y]):
                    count += 1
            if count:
                rows[y] = count
                total += count
        bands = []
        for y in sorted(rows):
            if bands and y - bands[-1][1] <= 2:
                bands[-1] = (bands[-1][0], y, bands[-1][2] + rows[y])
            else:
                bands.append((y, y, rows[y]))
        summary = ", ".join(
            "y=%d-%d px=%d" % (start, end, count) for start, end, count in bands[:6]
        )
        print("%-14s gold_px=%-6d bands=%d %s" % (name, total, len(bands), summary))


if __name__ == "__main__":
    main()
