import glob

import fitz

for path in sorted(glob.glob("pawapay-documents/*v1.1.pdf")):
    document = fitz.open(path)
    print("== %s  pages=%d" % (path, document.page_count))
    for index, page in enumerate(document):
        text = " ".join(page.get_text().split())
        images = len(page.get_images(full=True))
        print(
            "   p%-2d chars=%-5d images=%d  starts=%r"
            % (index + 1, len(text), images, text[:70])
        )
    document.close()
