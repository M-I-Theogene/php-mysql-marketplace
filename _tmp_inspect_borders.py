"""Inspect the generated .docx for paragraph borders and their XML order."""

import os
import zipfile

from lxml import etree

OUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "pawapay-documents")
W = "{http://schemas.openxmlformats.org/wordprocessingml/2006/main}"


def describe(document_xml):
    root = etree.fromstring(document_xml)
    total = 0
    shown = 0
    for paragraph in root.iter(W + "p"):
        properties = paragraph.find(W + "pPr")
        if properties is None:
            continue
        borders = properties.find(W + "pBdr")
        if borders is None:
            continue
        total += 1
        if shown < 3:
            shown += 1
            children = [child.tag.replace(W, "w:") for child in properties]
            edges = []
            for edge in borders:
                edges.append(
                    "%s val=%s sz=%s color=%s space=%s"
                    % (
                        edge.tag.replace(W, "w:"),
                        edge.get(W + "val"),
                        edge.get(W + "sz"),
                        edge.get(W + "color"),
                        edge.get(W + "space"),
                    )
                )
            text = "".join(paragraph.itertext())[:40]
            print("   pPr order: %s" % ", ".join(children))
            print("   pBdr: %s | text=%r" % ("; ".join(edges), text))
    return total


def main():
    for name in sorted(os.listdir(OUT_DIR)):
        if not name.lower().endswith(".docx"):
            continue
        tag = "KYC" if "KYC" in name else "VDD"
        with zipfile.ZipFile(os.path.join(OUT_DIR, name)) as archive:
            parts = [p for p in archive.namelist() if p.startswith("word/") and p.endswith(".xml")]
            print("=== %s ===" % tag)
            print("   parts: %s" % ", ".join(sorted(parts)))
            total = describe(archive.read("word/document.xml"))
            print("   paragraphs with pBdr in document.xml: %d" % total)
            for part in sorted(parts):
                if "header" in part or "footer" in part:
                    count = describe(archive.read(part))
                    print("   paragraphs with pBdr in %s: %d" % (part, count))


if __name__ == "__main__":
    main()
