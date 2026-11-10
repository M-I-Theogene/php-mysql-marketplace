"""Render a list of content blocks into a styled .docx using python-docx.

Blocks are produced by _tmp_html2docx.py from the source HTML files.
"""

import re

from docx import Document
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_TAB_ALIGNMENT
from docx.enum.table import WD_ROW_HEIGHT_RULE
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Cm, Pt, RGBColor

# ---------------------------------------------------------------- constants

BODY_FONT = "Calibri"
BODY_SIZE = Pt(10.5)
TEXT_COLOR = RGBColor(0x1B, 0x2A, 0x38)
BRONZE = RGBColor(0x8A, 0x63, 0x20)
MUTED = RGBColor(0x5A, 0x53, 0x48)
PLACEHOLDER = RGBColor(0xB3, 0x26, 0x1E)
WHITE = RGBColor(0xFF, 0xFF, 0xFF)

GOLD_HEX = "D8A830"
BORDER_HEX = "C9C2B4"
DARK_HEX = "1B2A38"
LABEL_HEX = "F3EEE3"
ALT_HEX = "FAF7F0"
STAMP_HEX = "9A9184"

PAGE_WIDTH_CM = 21.0
MARGIN_LR_CM = 2.12
CONTENT_WIDTH_CM = PAGE_WIDTH_CM - 2 * MARGIN_LR_CM

INLINE_RE = re.compile(r"(\*\*.+?\*\*|\*.+?\*|\[\[.+?\]\])")


# ---------------------------------------------------------------- xml helpers

def _shade(element_pr, hex_fill):
    """Apply w:shd to a tcPr or pPr element."""
    shd = OxmlElement("w:shd")
    shd.set(qn("w:val"), "clear")
    shd.set(qn("w:color"), "auto")
    shd.set(qn("w:fill"), hex_fill)
    element_pr.append(shd)


def shade_cell(cell, hex_fill):
    _shade(cell._tc.get_or_add_tcPr(), hex_fill)


def set_cell_borders(cell, color=BORDER_HEX, size="4", style="single"):
    tc_pr = cell._tc.get_or_add_tcPr()
    borders = OxmlElement("w:tcBorders")
    for edge in ("top", "left", "bottom", "right"):
        border = OxmlElement("w:" + edge)
        border.set(qn("w:val"), style)
        border.set(qn("w:sz"), size)
        border.set(qn("w:space"), "0")
        border.set(qn("w:color"), color)
        borders.append(border)
    tc_pr.append(borders)


def clear_cell_borders(cell):
    tc_pr = cell._tc.get_or_add_tcPr()
    borders = OxmlElement("w:tcBorders")
    for edge in ("top", "left", "bottom", "right"):
        border = OxmlElement("w:" + edge)
        border.set(qn("w:val"), "nil")
        borders.append(border)
    tc_pr.append(borders)


def set_table_borders(table, color=BORDER_HEX, size="4"):
    tbl_pr = table._tbl.tblPr
    borders = OxmlElement("w:tblBorders")
    for edge in ("top", "left", "bottom", "right", "insideH", "insideV"):
        border = OxmlElement("w:" + edge)
        border.set(qn("w:val"), "single")
        border.set(qn("w:sz"), size)
        border.set(qn("w:space"), "0")
        border.set(qn("w:color"), color)
        borders.append(border)
    tbl_pr.append(borders)


def set_paragraph_bottom_border(paragraph, color=GOLD_HEX, size="6"):
    p_pr = paragraph._p.get_or_add_pPr()
    borders = OxmlElement("w:pBdr")
    bottom = OxmlElement("w:bottom")
    bottom.set(qn("w:val"), "single")
    bottom.set(qn("w:sz"), size)
    bottom.set(qn("w:space"), "4")
    bottom.set(qn("w:color"), color)
    borders.append(bottom)
    p_pr.append(borders)


def add_field(paragraph, instruction):
    """Append a Word field (PAGE / NUMPAGES) to the paragraph."""
    run = paragraph.add_run()

    begin = OxmlElement("w:fldChar")
    begin.set(qn("w:fldCharType"), "begin")

    instr = OxmlElement("w:instrText")
    instr.set(qn("xml:space"), "preserve")
    instr.text = " %s " % instruction

    separate = OxmlElement("w:fldChar")
    separate.set(qn("w:fldCharType"), "separate")

    placeholder = OxmlElement("w:t")
    placeholder.text = "1"

    end = OxmlElement("w:fldChar")
    end.set(qn("w:fldCharType"), "end")

    for part in (begin, instr, separate, placeholder, end):
        run._r.append(part)

    return run


def vertical_center(cell):
    tc_pr = cell._tc.get_or_add_tcPr()
    align = OxmlElement("w:vAlign")
    align.set(qn("w:val"), "center")
    tc_pr.append(align)


# ---------------------------------------------------------------- text runs

def add_inline(paragraph, text, size=BODY_SIZE, color=None, bold=False):
    """Add text to a paragraph, honouring **bold**, *italic* and [[placeholder]]."""
    base_color = color if color is not None else TEXT_COLOR

    for piece in INLINE_RE.split(text):
        if not piece:
            continue

        if piece.startswith("**") and piece.endswith("**") and len(piece) > 4:
            run = paragraph.add_run(piece[2:-2])
            run.bold = True
            run.font.color.rgb = base_color
        elif piece.startswith("[[") and piece.endswith("]]"):
            run = paragraph.add_run(piece[2:-2])
            run.bold = True
            run.font.color.rgb = PLACEHOLDER
        elif piece.startswith("*") and piece.endswith("*") and len(piece) > 2:
            run = paragraph.add_run(piece[1:-1])
            run.italic = True
            run.font.color.rgb = base_color
        else:
            run = paragraph.add_run(piece)
            run.bold = bold
            run.font.color.rgb = base_color

        run.font.size = size
        run.font.name = BODY_FONT


# ---------------------------------------------------------------- document setup

def configure_styles(document):
    normal = document.styles["Normal"]
    normal.font.name = BODY_FONT
    normal.font.size = BODY_SIZE
    normal.font.color.rgb = TEXT_COLOR
    normal.paragraph_format.space_after = Pt(7)
    normal.paragraph_format.line_spacing = 1.15
    normal.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY

    for name, size, color in (
        ("Heading 1", Pt(20), TEXT_COLOR),
        ("Heading 2", Pt(12.5), TEXT_COLOR),
        ("Heading 3", Pt(10.5), BRONZE),
    ):
        style = document.styles[name]
        style.font.name = BODY_FONT
        style.font.size = size
        style.font.bold = True
        style.font.color.rgb = color
        style.font.italic = False


def configure_page(document):
    section = document.sections[0]
    section.page_width = Cm(21.0)
    section.page_height = Cm(29.7)
    section.top_margin = Cm(2.68)
    section.bottom_margin = Cm(2.19)
    section.left_margin = Cm(MARGIN_LR_CM)
    section.right_margin = Cm(MARGIN_LR_CM)
    section.header_distance = Cm(1.06)
    section.footer_distance = Cm(1.06)
    return section


def build_header(section, logo_path, ref):
    header = section.header
    header.is_linked_to_previous = False
    paragraph = header.paragraphs[0]
    paragraph.text = ""
    paragraph.alignment = WD_ALIGN_PARAGRAPH.LEFT
    paragraph.paragraph_format.space_after = Pt(2)
    paragraph.paragraph_format.tab_stops.add_tab_stop(
        Cm(CONTENT_WIDTH_CM), WD_TAB_ALIGNMENT.RIGHT
    )

    picture_run = paragraph.add_run()
    picture_run.add_picture(logo_path, width=Pt(74))

    text_run = paragraph.add_run(
        "\tCypadi Ltd  |  %s  |  Version 1.1" % ref
    )
    text_run.font.size = Pt(8)
    text_run.font.name = BODY_FONT
    text_run.font.color.rgb = RGBColor(0x80, 0x80, 0x80)

    set_paragraph_bottom_border(paragraph, GOLD_HEX, "12")


def build_footer(section, title):
    footer = section.footer
    footer.is_linked_to_previous = False
    paragraph = footer.paragraphs[0]
    paragraph.text = ""
    paragraph.alignment = WD_ALIGN_PARAGRAPH.LEFT
    paragraph.paragraph_format.tab_stops.add_tab_stop(
        Cm(CONTENT_WIDTH_CM), WD_TAB_ALIGNMENT.RIGHT
    )

    def muted(run):
        run.font.size = Pt(8)
        run.font.name = BODY_FONT
        run.font.color.rgb = RGBColor(0x80, 0x80, 0x80)

    muted(paragraph.add_run("Cypadi Ltd  |  %s  |  Internal - Confidential\tPage " % title))
    muted(add_field(paragraph, "PAGE"))
    muted(paragraph.add_run(" of "))
    muted(add_field(paragraph, "NUMPAGES"))
