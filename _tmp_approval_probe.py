import zipfile

import fitz

DOCX = "pawapay-documents/Cypadi Ltd - KYC and AML Policy - CYP-AML-001 v1.1.docx"
PDF = "pawapay-documents/Cypadi Ltd - KYC and AML Policy - CYP-AML-001 v1.1.pdf"

with zipfile.ZipFile(DOCX) as archive:
    xml = archive.read("word/document.xml").decode("utf-8")

start = xml.find("Approval")
print("--- xml around the approval heading ---")
print(xml[max(0, start - 900):start + 900].replace("><", ">\n<"))

document = fitz.open(PDF)
for number in (7, 8):
    page = document[number - 1]
    blocks = page.get_text("blocks")
    print("--- page %d text blocks ---" % number)
    for block in blocks:
        print("   y0=%-6.1f y1=%-6.1f %r" % (block[1], block[3], block[4][:60]))
    drawings = page.get_drawings()
    if drawings:
        tops = [d["rect"] for d in drawings]
        print(
            "   drawings y: %.1f .. %.1f"
            % (min(r.y0 for r in tops), max(r.y1 for r in tops))
        )
document.close()
