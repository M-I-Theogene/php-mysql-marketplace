"""Build the Cypadi KYC/AML policy and Vendor Due Diligence documents.

The proofread HTML sources (_tmp_doc1_kyc.html, _tmp_doc2_vdd.html) are parsed
into simple content blocks and rendered as styled Word documents: logo header,
page-numbered footer, document-control tables and an approval/signature page.
"""

import os
import re
from html.parser import HTMLParser

from docx import Document
from docx.enum.table import WD_ROW_HEIGHT_RULE, WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_BREAK, WD_TAB_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Cm, Pt, RGBColor

ROOT = os.path.dirname(os.path.abspath(__file__))
LOGO = os.path.join(ROOT, "assets", "images", "cypadi-logo-transparent.png")
OUT_DIR = os.path.join(ROOT, "pawapay-documents")

BODY_FONT = "Calibri"
BODY_SIZE = Pt(10.5)
TEXT = RGBColor(0x1B, 0x2A, 0x38)
BRONZE = RGBColor(0x8A, 0x63, 0x20)
MUTED = RGBColor(0x5A, 0x53, 0x48)
GREY = RGBColor(0x80, 0x80, 0x80)
PLACEHOLDER = RGBColor(0xB3, 0x26, 0x1E)
WHITE = RGBColor(0xFF, 0xFF, 0xFF)

GOLD = "D8A830"
DARK = "1B2A38"
BORDER = "C9C2B4"
LABEL = "F3EEE3"
ALT = "FAF7F0"
STAMP_GREY = "9A9184"

PAGE_W_CM = 21.0
PAGE_H_CM = 29.7
MARGIN_LR_CM = 2.12
MARGIN_TOP_CM = 2.68
MARGIN_BOTTOM_CM = 2.19
CONTENT_W_CM = PAGE_W_CM - 2 * MARGIN_LR_CM
CELL_TEXT = Pt(9.5)

INLINE_RE = re.compile(r"(\*\*.+?\*\*|\[\[.+?\]\]|(?<!\*)\*[^*]+\*(?!\*))")
WIDTH_RE = re.compile(r"width\s*:\s*([\d.]+)\s*%")

JOBS = [
    {
        "html": "_tmp_doc1_kyc.html",
        "base": "Cypadi Ltd - KYC and AML Policy - CYP-AML-001 v1.1",
        "ref": "CYP-AML-001",
        "title": "KYC and Anti-Money Laundering Policy",
    },
    {
        "html": "_tmp_doc2_vdd.html",
        "base": "Cypadi Ltd - Vendor Due Diligence Procedures - CYP-VDD-001 v1.1",
        "ref": "CYP-VDD-001",
        "title": "Vendor Due Diligence Procedures",
    },
]


# --------------------------------------------------------------------- xml

AFTER_PBDR = (
    "w:shd", "w:tabs", "w:suppressAutoHyphens", "w:kinsoku", "w:wordWrap",
    "w:overflowPunct", "w:topLinePunct", "w:autoSpaceDE", "w:autoSpaceDN",
    "w:bidi", "w:adjustRightInd", "w:snapToGrid", "w:spacing", "w:ind",
    "w:contextualSpacing", "w:mirrorIndents", "w:suppressOverlap", "w:jc",
    "w:textDirection", "w:textAlignment", "w:textboxTightWrap", "w:outlineLvl",
    "w:divId", "w:cnfStyle", "w:rPr", "w:sectPr", "w:pPrChange",
)
AFTER_KEEPNEXT = ("w:keepLines", "w:pageBreakBefore", "w:framePr", "w:widowControl",
                  "w:numPr") + AFTER_PBDR
AFTER_PPR_SHADING = AFTER_PBDR[1:]
AFTER_TCBORDERS = ("w:shd", "w:noWrap", "w:tcMar", "w:textDirection", "w:tcFitText",
                   "w:vAlign", "w:hideMark")
AFTER_TC_SHADING = AFTER_TCBORDERS[1:]
AFTER_VALIGN = ("w:hideMark",)
AFTER_TBLBORDERS = ("w:shd", "w:tblLayout", "w:tblCellMar", "w:tblLook",
                    "w:tblCaption", "w:tblDescription")
AFTER_TBLCELLMAR = ("w:tblLook", "w:tblCaption", "w:tblDescription")


def _add_shading(pr, hex_fill, successors):
    existing = pr.find(qn("w:shd"))
    if existing is not None:
        pr.remove(existing)
    shd = OxmlElement("w:shd")
    shd.set(qn("w:val"), "clear")
    shd.set(qn("w:color"), "auto")
    shd.set(qn("w:fill"), hex_fill)
    pr.insert_element_before(shd, *successors)


def shade_cell(cell, hex_fill):
    _add_shading(cell._tc.get_or_add_tcPr(), hex_fill, AFTER_TC_SHADING)


def shade_paragraph(paragraph, hex_fill):
    _add_shading(paragraph._p.get_or_add_pPr(), hex_fill, AFTER_PPR_SHADING)


def _border(edge, style, size, color):
    element = OxmlElement("w:" + edge)
    element.set(qn("w:val"), style)
    element.set(qn("w:sz"), str(size))
    element.set(qn("w:space"), "0")
    element.set(qn("w:color"), color)
    return element


def set_cell_borders(cell, color=BORDER, size=4, style="single"):
    tc_pr = cell._tc.get_or_add_tcPr()
    existing = tc_pr.find(qn("w:tcBorders"))
    if existing is not None:
        tc_pr.remove(existing)
    borders = OxmlElement("w:tcBorders")
    for edge in ("top", "left", "bottom", "right"):
        borders.append(_border(edge, style, size, color))
    tc_pr.insert_element_before(borders, *AFTER_TCBORDERS)


def set_table_borders(table, color=BORDER, size=4, style="single"):
    tbl_pr = table._tbl.tblPr
    existing = tbl_pr.find(qn("w:tblBorders"))
    if existing is not None:
        tbl_pr.remove(existing)
    borders = OxmlElement("w:tblBorders")
    for edge in ("top", "left", "bottom", "right", "insideH", "insideV"):
        borders.append(_border(edge, style, size, color))
    tbl_pr.insert_element_before(borders, *AFTER_TBLBORDERS)


def set_paragraph_border(paragraph, edge, color=GOLD, size=6, space=4):
    p_pr = paragraph._p.get_or_add_pPr()
    borders = p_pr.find(qn("w:pBdr"))
    if borders is None:
        borders = OxmlElement("w:pBdr")
        p_pr.insert_element_before(borders, *AFTER_PBDR)
    existing = borders.find(qn("w:" + edge))
    if existing is not None:
        borders.remove(existing)
    borders.append(_border(edge, "single", size, color))


def keep_with_next(paragraph):
    p_pr = paragraph._p.get_or_add_pPr()
    if p_pr.find(qn("w:keepNext")) is not None:
        return
    keep = OxmlElement("w:keepNext")
    p_pr.insert_element_before(keep, *AFTER_KEEPNEXT)


def vertical_center(cell):
    tc_pr = cell._tc.get_or_add_tcPr()
    if tc_pr.find(qn("w:vAlign")) is not None:
        return
    align = OxmlElement("w:vAlign")
    align.set(qn("w:val"), "center")
    tc_pr.insert_element_before(align, *AFTER_VALIGN)


def set_row_properties(row, is_header=False):
    """Stop rows splitting across pages, and repeat the header row."""
    tr_pr = row._tr.get_or_add_trPr()

    cant_split = OxmlElement("w:cantSplit")
    tr_pr.append(cant_split)

    if is_header:
        header = OxmlElement("w:tblHeader")
        tr_pr.append(header)


def add_field(paragraph, instruction):
    """Append a PAGE / NUMPAGES field to a paragraph."""
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


def set_cell_margins(table, top=60, bottom=60, left=90, right=90):
    mar = OxmlElement("w:tblCellMar")
    for name, value in (("top", top), ("left", left), ("bottom", bottom), ("right", right)):
        node = OxmlElement("w:" + name)
        node.set(qn("w:w"), str(value))
        node.set(qn("w:type"), "dxa")
        mar.append(node)
    table._tbl.tblPr.insert_element_before(mar, *AFTER_TBLCELLMAR)
# ------------------------------------------------------------------- runs

def add_runs(paragraph, text, size=BODY_SIZE, color=None, bold=False, italic=False):
    """Write text with **bold**, *italic* and [[placeholder]] markers."""
    base = TEXT if color is None else color
    for piece in INLINE_RE.split(text):
        if not piece:
            continue
        run_bold, run_italic, run_color = bold, italic, base
        if piece.startswith("**") and piece.endswith("**") and len(piece) > 4:
            piece = piece[2:-2]
            run_bold = True
        elif piece.startswith("[[") and piece.endswith("]]") and len(piece) > 4:
            run_bold = True
            run_color = PLACEHOLDER
        elif piece.startswith("*") and piece.endswith("*") and len(piece) > 2:
            piece = piece[1:-1]
            run_italic = True
        run = paragraph.add_run(piece)
        run.bold = run_bold
        run.italic = run_italic
        run.font.size = size
        run.font.name = BODY_FONT
        run.font.color.rgb = run_color


# ---------------------------------------------------------------- blocks

def render_title(document, text):
    paragraph = document.add_paragraph()
    paragraph.paragraph_format.space_before = Pt(6)
    paragraph.paragraph_format.space_after = Pt(2)
    paragraph.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.LEFT
    keep_with_next(paragraph)
    add_runs(paragraph, text, size=Pt(20), bold=True)


def render_subtitle(document, text):
    paragraph = document.add_paragraph()
    paragraph.paragraph_format.space_after = Pt(8)
    paragraph.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.LEFT
    add_runs(paragraph, text.upper(), size=Pt(9), color=BRONZE)


def render_rule(document):
    paragraph = document.add_paragraph()
    paragraph.paragraph_format.space_before = Pt(0)
    paragraph.paragraph_format.space_after = Pt(12)
    paragraph.paragraph_format.line_spacing = Pt(1)
    set_paragraph_border(paragraph, "bottom", GOLD, 24, 0)


def render_heading2(document, text):
    paragraph = document.add_paragraph()
    paragraph.paragraph_format.space_before = Pt(14)
    paragraph.paragraph_format.space_after = Pt(6)
    paragraph.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.LEFT
    keep_with_next(paragraph)
    add_runs(paragraph, text, size=Pt(12.5), color=TEXT, bold=True)
    set_paragraph_border(paragraph, "bottom", GOLD, 6, 3)


def render_heading3(document, text):
    paragraph = document.add_paragraph()
    paragraph.paragraph_format.space_before = Pt(10)
    paragraph.paragraph_format.space_after = Pt(4)
    paragraph.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.LEFT
    keep_with_next(paragraph)
    add_runs(paragraph, text, size=Pt(10.5), color=BRONZE, bold=True)


def render_paragraph(document, text, small=False, align_center=False, space_before=0):
    paragraph = document.add_paragraph()
    if space_before:
        paragraph.paragraph_format.space_before = Pt(space_before)
    paragraph.paragraph_format.space_after = Pt(4 if small else 7)
    paragraph.paragraph_format.alignment = (
        WD_ALIGN_PARAGRAPH.CENTER if align_center else WD_ALIGN_PARAGRAPH.JUSTIFY
    )
    if small:
        add_runs(paragraph, text, size=Pt(9), color=MUTED)


    else:
        add_runs(paragraph, text)
    return paragraph


def render_bullets(document, items):
    for item in items:
        paragraph = document.add_paragraph()
        fmt = paragraph.paragraph_format
        fmt.left_indent = Cm(0.75)
        fmt.first_line_indent = Cm(-0.35)
        fmt.space_after = Pt(4)
        fmt.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
        bullet = paragraph.add_run("\u2022\t")
        bullet.font.size = BODY_SIZE
        bullet.font.name = BODY_FONT
        bullet.font.color.rgb = BRONZE
        add_runs(paragraph, item)


def _column_widths(head, rows, label_col):
    """Turn the HTML percentage hints into absolute centimetres."""
    count = len(head) if head else len(rows[0]["cells"])
    pcts = []

    def take(cells):
        for index, cell in enumerate(cells):
            found = WIDTH_RE.search(cell.get("style") or "")
            if found and index >= len(pcts):
                pcts.append((index, float(found.group(1))))

    if head:
        take(head)
    for row in rows:
        take(row["cells"])

    fixed = {}
    for index, value in pcts:
        fixed[index] = value

    if len(fixed) == count:
        weights = [fixed[index] for index in range(count)]
    elif fixed:
        taken = sum(fixed.values())
        spare = max(0.0, 100.0 - taken)
        gaps = count - len(fixed)
        weights = [fixed.get(index, spare / gaps if gaps else 0.0) for index in range(count)]
    elif label_col and count == 2:
        weights = [32.0, 68.0]
    else:
        weights = [100.0 / count] * count

    total = sum(weights) or 100.0
    return [CONTENT_W_CM * weight / total for weight in weights]


def render_table(document, head, rows, label_col):
    if not rows and not head:
        return
    count = len(head) if head else len(rows[0]["cells"])
    table = document.add_table(rows=0, cols=count)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    table.autofit = False
    set_table_borders(table)
    set_cell_margins(table)

    widths = _column_widths(head, rows, label_col)

    def write_row(cells, is_head=False, alt=False):
        row = table.add_row()
        set_row_properties(row, is_head)
        for index, cell_data in enumerate(cells):
            cell = row.cells[index]
            cell.width = Cm(widths[index])
            paragraph = cell.paragraphs[0]
            paragraph.paragraph_format.space_after = Pt(0)
            paragraph.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.LEFT
            text = cell_data.get("text", "")
            if is_head:
                keep_with_next(paragraph)
                shade_cell(cell, DARK)
                add_runs(paragraph, text, size=CELL_TEXT, color=WHITE, bold=True)
            elif cell_data.get("label"):
                shade_cell(cell, LABEL)
                add_runs(paragraph, text, size=CELL_TEXT, bold=True)
            else:
                if alt:
                    shade_cell(cell, ALT)
                add_runs(paragraph, text, size=CELL_TEXT)
        return row

    if head:
        write_row(head, is_head=True)
    for position, row in enumerate(rows):
        write_row(row["cells"], alt=(position % 2 == 1) or row.get("alt", False))

    for index, width in enumerate(widths):
        table.columns[index].width = Cm(width)

    spacer = document.add_paragraph()
    spacer.paragraph_format.space_after = Pt(4)
    spacer.paragraph_format.line_spacing = Pt(6)


def render_signature(document):
    table = document.add_table(rows=1, cols=2)
    table.autofit = False
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    left, right = table.rows[0].cells
    left.width = Cm(CONTENT_W_CM * 0.56)
    right.width = Cm(CONTENT_W_CM * 0.44)

    heading = left.paragraphs[0]
    heading.paragraph_format.space_after = Pt(4)
    add_runs(heading, "SIGNED FOR AND ON BEHALF OF CYPADI LTD", size=Pt(9), color=MUTED, bold=True)

    line = left.add_paragraph()
    line.paragraph_format.space_before = Pt(22)
    line.paragraph_format.space_after = Pt(2)
    set_paragraph_border(line, "bottom", DARK, 6, 0)
    cap = line.add_run("")
    cap.font.size = Pt(9)

    caption = left.add_paragraph()
    caption.paragraph_format.space_after = Pt(10)
    add_runs(caption, "Signature", size=Pt(9), color=MUTED)

    name = left.add_paragraph()
    name.paragraph_format.space_after = Pt(2)
    add_runs(name, "[[MD_NAME]]", size=BODY_SIZE, bold=True)

    role = left.add_paragraph()
    role.paragraph_format.space_after = Pt(2)
    add_runs(role, "Managing Director, Cypadi Ltd", size=BODY_SIZE)

    dated = left.add_paragraph()
    dated.paragraph_format.space_before = Pt(12)
    dated.paragraph_format.space_after = Pt(2)
    add_runs(dated, "Date: [[SIGN_DATE]]", size=BODY_SIZE)

    stamp_table = right.add_table(rows=1, cols=1)
    stamp_table.autofit = False
    set_table_borders(stamp_table, STAMP_GREY, 8, "dashed")
    stamp_cell = stamp_table.rows[0].cells[0]
    stamp_cell.width = Cm(CONTENT_W_CM * 0.38)
    stamp_table.rows[0].height = Pt(104)
    stamp_table.rows[0].height_rule = WD_ROW_HEIGHT_RULE.AT_LEAST
    vertical_center(stamp_cell)
    label = stamp_cell.paragraphs[0]
    label.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.CENTER
    label.paragraph_format.space_before = Pt(42)
    add_runs(label, "COMPANY STAMP", size=Pt(8.5), color=RGBColor(0x9A, 0x91, 0x84), bold=False)
# ------------------------------------------------------------- page setup

def configure_document(document, job):
    section = document.sections[0]
    section.page_width = Cm(PAGE_W_CM)
    section.page_height = Cm(PAGE_H_CM)
    section.top_margin = Cm(MARGIN_TOP_CM)
    section.bottom_margin = Cm(MARGIN_BOTTOM_CM)
    section.left_margin = Cm(MARGIN_LR_CM)
    section.right_margin = Cm(MARGIN_LR_CM)
    section.header_distance = Cm(1.06)
    section.footer_distance = Cm(1.06)

    normal = document.styles["Normal"]
    normal.font.name = BODY_FONT
    normal.font.size = BODY_SIZE
    normal.font.color.rgb = TEXT
    normal.paragraph_format.space_after = Pt(7)
    normal.paragraph_format.line_spacing = 1.15

    build_header(section, job["ref"])
    build_footer(section, job["title"])

    core = document.core_properties
    core.title = job["title"]
    core.author = "Cypadi Ltd"
    core.company = "Cypadi Ltd"
    core.subject = "PawaPay due diligence submission"
    core.comments = "Cypadi Ltd - %s - Version 1.1" % job["ref"]
    core.category = "Compliance policy"


def build_header(section, ref):
    header = section.header
    header.is_linked_to_previous = False
    paragraph = header.paragraphs[0]
    paragraph.text = ""
    paragraph.alignment = WD_ALIGN_PARAGRAPH.LEFT
    paragraph.paragraph_format.space_after = Pt(2)
    paragraph.paragraph_format.tab_stops.add_tab_stop(
        Cm(CONTENT_W_CM), WD_TAB_ALIGNMENT.RIGHT
    )

    picture = paragraph.add_run()
    picture.add_picture(LOGO, width=Pt(76))

    text = paragraph.add_run("\tCypadi Ltd  |  %s  |  Version 1.1" % ref)
    text.font.size = Pt(8)
    text.font.name = BODY_FONT
    text.font.color.rgb = GREY

    set_paragraph_border(paragraph, "bottom", GOLD, 12, 4)


def build_footer(section, title):
    footer = section.footer
    footer.is_linked_to_previous = False
    paragraph = footer.paragraphs[0]
    paragraph.text = ""
    paragraph.alignment = WD_ALIGN_PARAGRAPH.LEFT
    paragraph.paragraph_format.tab_stops.add_tab_stop(
        Cm(CONTENT_W_CM), WD_TAB_ALIGNMENT.RIGHT
    )

    def muted(run):
        run.font.size = Pt(8)
        run.font.name = BODY_FONT
        run.font.color.rgb = GREY

    muted(paragraph.add_run("Cypadi Ltd  |  %s  |  Internal - Confidential\tPage " % title))
    muted(add_field(paragraph, "PAGE"))
    muted(paragraph.add_run(" of "))
    muted(add_field(paragraph, "NUMPAGES"))


# ------------------------------------------------------------------ parse

MARKERS = {"strong": "**", "b": "**", "em": "*", "i": "*"}


def clean_text(value):
    return re.sub(r"[ \t\r\f\v]+", " ", value or "").replace("\u00a0", " ").strip()


class BlockParser(HTMLParser):
    """Turn the regular, known markup of the two sources into content blocks."""

    def __init__(self):
        HTMLParser.__init__(self, convert_charrefs=True)
        self.blocks = []
        self.buffer = []
        self.mode = None
        self.small = False
        self.items = None
        self.item = None
        self.table_depth = 0
        self.table_stack = []
        self.rows = None
        self.row = None
        self.cell = None
        self.cell_label = False
        self.cell_head = False
        self.cell_style = ""
        self.row_alt = False
        self.signature_depth = None
        self.signature_capture = False

    # -- helpers ---------------------------------------------------------
    def _inline(self, marker):
        target = self.cell if self.cell is not None else (
            self.item if self.item is not None else self.buffer
        )
        target.append(marker)

    def _flush(self):
        text = clean_text("".join(self.buffer))
        self.buffer = []
        mode, is_small = self.mode, self.small
        self.mode = None
        self.small = False
        if not text:
            return
        if mode == "title":
            self.blocks.append({"type": "title", "text": text})
        elif mode == "subtitle":
            self.blocks.append({"type": "subtitle", "text": text})
        elif mode == "h2":
            self.blocks.append({"type": "h2", "text": text})
        elif mode == "h3":
            self.blocks.append({"type": "h3", "text": text})
        elif mode == "p":
            self.blocks.append({"type": "p", "text": text, "small": is_small})
        elif mode == "li":
            if self.item is not None and text:
                self.item.append(text)

    # -- callbacks -------------------------------------------------------
    def handle_starttag(self, tag, attrs):
        attributes = dict(attrs)
        classes = (attributes.get("class") or "").split()
        style = attributes.get("style") or ""

        if tag == "h1":
            self.mode = "title"
            self.buffer = []
        elif tag == "h2":
            self.mode = "h2"
            self.buffer = []
        elif tag == "h3":
            self.mode = "h3"
            self.buffer = []
        elif tag == "p":
            self.mode = "p"
            self.small = "small" in classes
            self.buffer = []
        elif tag == "div":
            if "subtitle" in classes:
                self.mode = "subtitle"
                self.buffer = []
            elif "goldrule" in classes:
                self.blocks.append({"type": "rule"})
            elif "page-break-before" in style:
                self.blocks.append({"type": "pagebreak"})
        elif tag == "ul":
            self.items = []
        elif tag == "li":
            self.item = []
        elif tag == "table":
            self.table_depth += 1
            if self.table_depth == 1:
                self.rows = []
                self.signature_capture = False
        elif tag == "tr":
            self.row = []
            self.row_alt = "alt" in classes
        elif tag in ("td", "th"):
            self.cell = []
            self.cell_head = tag == "th"
            self.cell_label = "lbl" in classes
            self.cell_style = style
            if "stampcell" in classes:
                self.signature_capture = True
        elif tag == "br":
            if self.cell is not None:
                self.cell.append(" ")
        elif tag in MARKERS:
            self._inline(MARKERS[tag])

    def handle_startendtag(self, tag, attrs):
        if tag == "br":
            if self.cell is not None:
                self.cell.append(" ")
            elif self.mode is not None:
                self.buffer.append(" ")
        else:
            self.handle_starttag(tag, attrs)
            self.handle_endtag(tag)

    def handle_endtag(self, tag):
        if tag in ("h1", "h2", "h3", "p"):
            self._flush()
        elif tag == "div":
            if self.mode == "subtitle":
                self._flush()
        elif tag == "li":
            self._flush()
            if self.item is not None and self.items is not None:
                text = clean_text("".join(self.item))
                if text:
                    self.items.append(text)
            self.item = None
        elif tag == "ul":
            if self.items:
                self.blocks.append({"type": "ul", "items": self.items})
            self.items = None
        elif tag in ("td", "th"):
            if self.row is not None:
                self.row.append(
                    {
                        "text": clean_text("".join(self.cell or [])),
                        "label": self.cell_label,
                        "head": self.cell_head,
                        "style": self.cell_style,
                    }
                )
            self.cell = None
        elif tag == "tr":
            if self.rows is not None and self.row is not None:
                self.rows.append({"cells": self.row, "alt": self.row_alt})
            self.row = None
        elif tag == "table":
            self.table_depth -= 1
            if self.table_depth == 0:
                self._flush_table()
        elif tag in MARKERS:
            self._inline(MARKERS[tag])

    def handle_data(self, data):
        if self.cell is not None:
            self.cell.append(data)
        elif self.item is not None:
            self.item.append(data)
        elif self.mode is not None:
            self.buffer.append(data)

    # -- table assembly --------------------------------------------------
    def _flush_table(self):
        rows = self.rows or []
        self.rows = None
        if self.signature_capture:
            self.signature_capture = False
            self.blocks.append({"type": "signature"})
            return
        if not rows:
            return
        head = None
        if rows[0]["cells"] and all(cell["head"] for cell in rows[0]["cells"]):
            head = rows[0]["cells"]
            rows = rows[1:]
        label_col = any(cell["label"] for cell in rows[0]["cells"]) if rows else False
        self.blocks.append(
            {"type": "table", "head": head, "rows": rows, "label_col": label_col}
        )
# ---------------------------------------------------------------- assemble

def parse_blocks(path):
    with open(path, encoding="utf-8") as handle:
        source = handle.read()
    match = re.search(r"<body[^>]*>(.*)</body>", source, re.S | re.I)
    body = match.group(1) if match else source
    parser = BlockParser()
    parser.feed(body)
    parser.close()
    return parser.blocks


def render_blocks(document, blocks):
    previous = None
    for block in blocks:
        kind = block["type"]
        if kind == "title":
            render_title(document, block["text"])
        elif kind == "subtitle":
            render_subtitle(document, block["text"])
        elif kind == "rule":
            render_rule(document)
        elif kind == "h2":
            render_heading2(document, block["text"])
        elif kind == "h3":
            render_heading3(document, block["text"])
        elif kind == "p":
            render_paragraph(
                document,
                block["text"],
                small=block.get("small", False),
                space_before=20 if previous == "signature" else 0,
            )
        elif kind == "ul":
            render_bullets(document, block["items"])
        elif kind == "table":
            render_table(document, block["head"], block["rows"], block["label_col"])
        elif kind == "pagebreak":
            paragraph = document.add_paragraph()
            paragraph.paragraph_format.space_after = Pt(0)
            paragraph.add_run().add_break(WD_BREAK.PAGE)
        elif kind == "signature":
            render_signature(document)
        previous = kind


def collect_placeholders(blocks):
    found = []

    def scan(value):
        if isinstance(value, str):
            for match in re.findall(r"\[\[(.+?)\]\]", value):
                found.append(match)
        elif isinstance(value, dict):
            for item in value.values():
                scan(item)
        elif isinstance(value, list):
            for item in value:
                scan(item)

    scan(blocks)
    return sorted(set(found))


def build(job):
    blocks = parse_blocks(os.path.join(ROOT, job["html"]))
    document = Document()
    configure_document(document, job)
    render_blocks(document, blocks)
    path = os.path.join(OUT_DIR, job["base"] + ".docx")
    document.save(path)
    placeholders = collect_placeholders(blocks)
    stats = {
        "blocks": len(blocks),
        "tables": sum(1 for block in blocks if block["type"] == "table"),
        "headings": sum(1 for block in blocks if block["type"] in ("h2", "h3")),
        "signature": sum(1 for block in blocks if block["type"] == "signature"),
        "pagebreaks": sum(1 for block in blocks if block["type"] == "pagebreak"),
        "chars": len(re.sub(r"<[^>]+>", " ", open(os.path.join(ROOT, job["html"]), encoding="utf-8").read())),
    }
    return path, placeholders, stats


def main():
    if not os.path.isdir(OUT_DIR):
        os.makedirs(OUT_DIR)
    for job in JOBS:
        path, placeholders, stats = build(job)
        print("BUILT %s" % os.path.basename(path))
        print("   blocks=%(blocks)d tables=%(tables)d headings=%(headings)d" % stats)
        print("   signature=%(signature)d pagebreaks=%(pagebreaks)d chars=%(chars)d" % stats)
        print("   placeholders(%d): %s" % (len(placeholders), ", ".join(placeholders)))
    print("BUILD_DONE")


if __name__ == "__main__":
    main()
