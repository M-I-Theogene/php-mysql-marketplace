"""Isolation test: which paragraph-border form survives Word -> PDF."""

import os

from docx import Document
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Pt

ROOT = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(ROOT, "_tmp_border_test.docx")


def add_border(paragraph, size="24", space="1"):
    p_pr = paragraph._p.get_or_add_pPr()
    borders = OxmlElement("w:pBdr")
    bottom = OxmlElement("w:bottom")
    bottom.set(qn("w:val"), "single")
    bottom.set(qn("w:sz"), size)
    bottom.set(qn("w:space"), space)
    bottom.set(qn("w:color"), "D8A830")
    borders.append(bottom)
    p_pr.insert_element_before(borders, "w:shd", "w:tabs", "w:spacing", "w:ind", "w:jc")

document = Document()
paragraph = document.add_paragraph("A: border sz=24 space=1, before spacing set")
add_border(paragraph)

paragraph = document.add_paragraph("B: border sz=8 space=1, after spacing set")
paragraph.paragraph_format.space_after = Pt(6)
add_border(paragraph, size="8")

empty = document.add_paragraph()
add_border(empty, size="24")

document.save(OUT)
print("SAVED", OUT)
