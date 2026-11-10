"""Print the text of the RDB certificate so any TIN can be read off it."""

import os

import fitz

PATH = os.path.join(
    os.path.dirname(os.path.abspath(__file__)),
    "pawapay-documents",
    "CYPADI Ltd.pdf",
)


def main():
    document = fitz.open(PATH)
    print("pages=%d" % document.page_count)
    for index, page in enumerate(document):
        print("--- page %d" % (index + 1))
        print(page.get_text().strip())
    document.close()


if __name__ == "__main__":
    main()
