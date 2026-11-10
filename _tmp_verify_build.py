"""Verify the rebuilt .docx packages: embedded media, header logo, signature block."""

import glob
import os
import re
import zipfile

OUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "pawapay-documents")
PATTERN = re.compile(r"\[\[(.+?)\]\]")


def inspect(path):
    print("=" * 78)
    print(os.path.basename(path))
    with zipfile.ZipFile(path) as archive:
        names = archive.namelist()
        media = [name for name in names if name.startswith("word/media/")]
        print("  media entries: %d" % len(media))
        for name in sorted(media):
            info = archive.getinfo(name)
            print("    %-28s %7d bytes" % (name, info.file_size))

        headers = sorted(n for n in names if re.match(r"word/header\d+\.xml$", n))
        footers = sorted(n for n in names if re.match(r"word/footer\d+\.xml$", n))
        for name in headers:
            xml = archive.read(name).decode("utf-8", "ignore")
            print("  %s pictures=%d fields=%d" % (
                name, xml.count("<pic:pic"), xml.count("fldChar")))
        for name in footers:
            xml = archive.read(name).decode("utf-8", "ignore")
            print("  %s PAGE=%d NUMPAGES=%d" % (
                name, xml.count("> PAGE <") + xml.count("PAGE"), xml.count("NUMPAGES")))

        document = archive.read("word/document.xml").decode("utf-8", "ignore")
        print("  body pictures=%d drawings=%d" % (
            document.count("<pic:pic"), document.count("<w:drawing>")))
        print("  page breaks=%d" % document.count('w:type="page"'))

        text = re.sub(r"<[^>]+>", " ", document)
        found = sorted(set(PATTERN.findall(text)))
        print("  placeholders in body: %s" % (", ".join(found) if found else "none"))


def main():
    for path in sorted(glob.glob(os.path.join(OUT_DIR, "*.docx"))):
        inspect(path)


if __name__ == "__main__":
    main()
