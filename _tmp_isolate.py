"""Build a minimal .docx with paragraph-border variants for a Word round trip."""

import os

from docx import Document
from docx.enum.table import WD_ROW_HEIGHT_RULE, WD_TABLE_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Pt

ROOT = os.path.dirname(os.path.abspath(__file__))
TARGET = os.path.join(ROOT, "_tmp_isolate.docx")
GOLD = "D8A830"

AFTER_PBDR = (
    "w:shd", "w:tabs", "w:suppressAutoHyphens", "w:kinsoku", "w:wordWrap",
    "w:overflowPunct", "w:topLinePunct", "w:autoSpaceDE", "w:autoSpaceDN",
    "w:bidi", "w:adjustRightInd", "w:snapToGrid", "w:spacing", "w:ind",
    "w:contextualSpacing", "w:mirrorIndents", "w:suppressOverlap", "w:jc",
    "w:textDirection", "w:textAlignment", "w:textboxTightWrap", "w:outlineLvl",
    "w:divId", "w:cnfStyle", "w:rPr", "w:sectPr",
)


def add_bottom_border(paragraph, size):
    properties = paragraph._p.get_or_add_pPr()
    borders = OxmlElement("w:pBdr")
    bottom = OxmlElement("w:bottom")
    bottom.set(qn("w:val"), "single")
    bottom.set(qn("w:sz"), size)
    bottom.set(qn("w:space"), "0")
    bottom.set(qn("w:color"), GOLD)
    borders.append(bottom)
    properties.insert_element_before(borders, *AFTER_PBDR)


def add_rule_table(document, height_pt):
    table = document.add_table(rows=1, cols=1)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    table.autofit = False
    borders = OxmlElement("w:tblBorders")
    for edge in ("top", "left", "bottom", "right", "insideH", "insideV"):
        node = OxmlElement("w:" + edge)
        node.set(qn("w:val"), "nil")
        borders.append(node)
    table._tbl.tblPr.append(borders)

    row = table.rows[0]
    row.height = Pt(height_pt)
    row.height_rule = WD_ROW_HEIGHT_RULE.EXACTLY
    cell = row.cells[0]
    shading = OxmlElement("w:shd")
    shading.set(qn("w:val"), "clear")
    shading.set(qn("w:color"), "auto")
    shading.set(qn("w:fill"), GOLD)
    cell._tc.get_or_add_tcPr().append(shading)
    cell.paragraphs[0].paragraph_format.space_after = Pt(0)
    run = cell.paragraphs[0].add_run("")
    run.font.size = Pt(1)


def main():
    document = Document()
    document.add_paragraph("VARIANT A - pBdr sz=24 on a text paragraph")
    first = document.add_paragraph("Heading A")
    add_bottom_border(first, "24")

    document.add_paragraph("VARIANT B - pBdr sz=6 on a text paragraph")
    second = document.add_paragraph("Heading B")
    add_bottom_border(second, "6")

    document.add_paragraph("VARIANT C - pBdr sz=24 on an empty paragraph")
    empty = document.add_paragraph()
    add_bottom_border(empty, "24")

    document.add_paragraph("VARIANT D - gold rule table, exact 2pt row")
    add_rule_table(document, 2)

    document.add_paragraph("END")
    document.save(TARGET)
    print("SAVED %s" % TARGET)


if __name__ == "__main__":
    main()
