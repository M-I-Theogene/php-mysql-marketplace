"""Build a probe .docx that isolates why Word drops paragraph borders.

Four variants are written, each labelled with its own text so the rendered PDF
can be inspected: raw pBdr, pBdr with spacing/jc, pBdr appended last (invalid
order on purpose), and a gold-shaded single-cell table as the fallback rule.
"""

import os

from docx import Document
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Pt

ROOT = os.path.dirname(os.path.abspath(__file__))
TARGET = os.path.join(ROOT, "_tmp_probe_border.docx")
GOLD = "D8A830"

AFTER_PBDR = (
    "w:shd", "w:tabs", "w:suppressAutoHyphens", "w:kinsoku", "w:wordWrap",
    "w:overflowPunct", "w:topLinePunct", "w:autoSpaceDE", "w:autoSpaceDN",
    "w:bidi", "w:adjustRightInd", "w:snapToGrid", "w:spacing", "w:ind",
    "w:contextualSpacing", "w:mirrorIndents", "w:suppressOverlap", "w:jc",
)
AFTER_TBLBORDERS = ("w:shd", "w:tblLayout", "w:tblCellMar", "w:tblLook")


def bottom_border(space="4", size="12"):
    borders = OxmlElement("w:pBdr")
    bottom = OxmlElement("w:bottom")
    bottom.set(qn("w:val"), "single")
    bottom.set(qn("w:sz"), size)
    bottom.set(qn("w:space"), space)
    bottom.set(qn("w:color"), GOLD)
    borders.append(bottom)
    return borders


def add_pbdr(paragraph, position, space="4"):
    properties = paragraph._p.get_or_add_pPr()
    borders = bottom_border(space)
    if position == "ordered":
        properties.insert_element_before(borders, *AFTER_PBDR)
    else:
        properties.append(borders)


def shade(cell, fill):
    properties = cell._tc.get_or_add_tcPr()
    shading = OxmlElement("w:shd")
    shading.set(qn("w:val"), "clear")
    shading.set(qn("w:color"), "auto")
    shading.set(qn("w:fill"), fill)
    properties.append(shading)


def no_borders(table):
    borders = OxmlElement("w:tblBorders")
    for edge in ("top", "left", "bottom", "right", "insideH", "insideV"):
        node = OxmlElement("w:" + edge)
        node.set(qn("w:val"), "nil")
        borders.append(node)
    table._tbl.tblPr.insert_element_before(borders, *AFTER_TBLBORDERS)


def main():
    document = Document()
    document.styles["Normal"].font.size = Pt(11)

    document.add_paragraph("VARIANT A - pBdr only, ordered")
    a = document.add_paragraph("Heading A")
    add_pbdr(a, "ordered")

    document.add_paragraph("VARIANT B - pBdr with spacing and jc, ordered")
    b = document.add_paragraph("Heading B")
    b.paragraph_format.space_before = Pt(14)
    b.paragraph_format.space_after = Pt(6)
    b.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.LEFT
    add_pbdr(b, "ordered")

    document.add_paragraph("VARIANT C - pBdr appended last (invalid order)")
    c = document.add_paragraph("Heading C")
    c.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.LEFT
    add_pbdr(c, "appended")

    document.add_paragraph("VARIANT D - gold shaded single-cell table")
    table = document.add_table(rows=1, cols=1)
    no_borders(table)
    shade(table.rows[0].cells[0], GOLD)
    row = table.rows[0]
    from docx.enum.table import WD_ROW_HEIGHT_RULE

    row.height = Pt(2)
    row.height_rule = WD_ROW_HEIGHT_RULE.EXACTLY
    row.cells[0].paragraphs[0].paragraph_format.space_after = Pt(0)
    for run in row.cells[0].paragraphs[0].runs:
        run.font.size = Pt(1)

    document.add_paragraph("END OF PROBE")
    document.save(TARGET)
    print("PROBE_SAVED %s" % TARGET)


if __name__ == "__main__":
    main()
