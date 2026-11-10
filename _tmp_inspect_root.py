import docx

for name in ("KYC and AML Policy (Final).docx",
             "Vendor Due Diligence Procedures (Final).docx"):
    document = docx.Document(name)
    xml = document.element.xml
    print("==", name)
    print("   paragraphs=%d tables=%d sections=%d"
          % (len(document.paragraphs), len(document.tables), len(document.sections)))
    print("   explicit page breaks in body: %d" % xml.count('w:type="page"'))
    print("   header images: %d" % sum(
        len(section.header.paragraphs[0].runs)
        for section in document.sections
    ))
    print("   last paragraphs:")
    for paragraph in document.paragraphs[-6:]:
        print("     %r" % paragraph.text[:90])
