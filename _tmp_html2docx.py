"""Convert the two source HTML files into styled .docx files.

The HTML files are the proofread source of truth; this turns their structure
into content blocks and renders them through _tmp_docx_render.
"""

import os
import re
import sys
from html.parser import HTMLParser

from docx import Document
from docx.enum.table import WD_ROW_HEIGHT_RULE
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.shared import Cm, Pt, RGBColor

import _tmp_docx_render as R

ROOT = os.path.dirname(os.path.abspath(__file__))
LOGO = os.path.join(ROOT, "assets", "images", "cypadi-logo-transparent.png")
OUT_DIR = os.path.join(ROOT, "pawapay-documents")

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

EMPHASIS = {"strong": "**", "b": "**", "em": "*", "i": "*"}


def clean(text):
    return re.sub(r"\s+", " ", text or "").strip()


class BlockExtractor(HTMLParser):
    """Turn the known, regular body markup into a flat list of blocks."""

    def __init__(self):
        HTMLParser.__init__(self, convert_charrefs=True)
        self.blocks = []
        self.buffer = []
        self.mode = None
        self.items = None
        self.cell_buffer = None
        self.cell_header = False
        self.row_cells = None
        self.row_headers = None
        self.row_alt = False
        self.table_rows = None
        self.label_col = False
        self.has_stamp = False
        self.inline_stack = []

    # -- buffer routing --------------------------------------------------
    def _target(self):
        return self.cell_buffer if self.cell_buffer is not None else self.buffer

    def _flush_text(self):
        text = clean("".join(self.buffer))
        self.buffer = []
        mode, self.mode = self.mode, None

        if mode == "li":
            if self.items is not None and text:
                self.items.append(text)
            return

        if not text:
            return

        kind = {
            "title": "title",
            "subtitle": "subtitle",
            "h2": "h2",
            "h3": "h3",
            "small": "small",
            "p": "p",
        }.get(mode)

        if kind:
            self.blocks.append({"type": kind, "text": text})

    # -- parser callbacks ------------------------------------------------
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
            self.mode = "small" if "small" in classes else "p"
            self.buffer = []
        elif tag == "div":
            if "subtitle" in classes:
                self.mode = "subtitle"
                self.buffer = []
            elif "page-break-before" in style:
                self.blocks.append({"type": "pagebreak"})
        elif tag == "ul":
            self.items = []
        elif tag == "li":
            self.mode = "li"
            self.buffer = []
        elif tag == "table":
            self.table_rows = []
        elif tag == "tr":
            self.row_cells = []
            self.row_headers = []
            self.row_alt = "alt" in classes
        elif tag in ("td", "th"):
            self.cell_buffer = []
            self.cell_header = tag == "th"
            if "lbl" in classes:
                self.label_col = True
            if "stampcell" in classes:
                self.has_stamp = True
        elif tag in EMPHASIS:
            target = self._target()
            target.append(EMPHASIS[tag])
            self.inline_stack.append((tag, EMPHASIS[tag], target))
        elif tag == "span" and "ph" in classes:
            target = self._target()
            target.append("[[")
            self.inline_stack.append(("span", "]]", target))

    def handle_endtag(self, tag):
        if tag in ("h1", "h2", "h3", "p"):
            self._flush_text()
        elif tag == "div":
            if self.mode == "subtitle":
                self._flush_text()
        elif tag == "ul":
            if self.items:
                self.blocks.append({"type": "ul", "items": self.items})
            self.items = None
        elif tag in ("td", "th"):
            if self.row_cells is not None:
                self.row_cells.append(clean("".join(self.cell_buffer or [])))
                self.row_headers.append(self.cell_header)
            self.cell_buffer = None
        elif tag == "tr":
            if self.table_rows is not None and self.row_cells is not None:
                self.table_rows.append(
                    {
                        "cells": self.row_cells,
                        "headers": self.row_headers,
                        "alt": self.row_alt,
                    }
                )
            self.row_cells = None
            self.row_headers = None
        elif tag == "table":
            self._flush_table()
        elif tag == "li":
            self._flush_text()
        else:
            self._close_inline(tag)

    def _close_inline(self, tag):
        if not self.inline_stack:
            return
        open_tag, marker, target = self.inline_stack[-1]
        if open_tag == tag:
            target.append(marker)
            self.inline_stack.pop()

    def handle_data(self, data):
        if self.cell_buffer is not None:
            self.cell_buffer.append(data)
        elif self.mode is not None:
            self.buffer.append(data)

    # -- table assembly --------------------------------------------------
    def _flush_table(self):
        rows = self.table_rows or []
        self.table_rows = None

        if self.has_stamp:
            self.blocks.append({"type": "signature"})
            self.has_stamp = False
            self.label_col = False
            return

        if not rows:
            self.label_col = False
            return

        head = None
        if rows[0]["headers"] and all(rows[0]["headers"]):
            head = rows[0]["cells"]
            rows = rows[1:]

        self.blocks.append(
            {
                "type": "table",
                "head": head,
                "rows": rows,
                "label_col": self.label_col,
            }
        )
        self.label_col = False


def parse_blocks(path):
    with open(path, encoding="utf-8") as handle:
        source = handle.read()

    match = re.search(r"<body[^>]*>(.*)</body>", source, re.S | re.I)
    body = match.group(1) if match else source

    extractor = BlockExtractor()
    extractor.feed(body)
    return extractor.blocks
