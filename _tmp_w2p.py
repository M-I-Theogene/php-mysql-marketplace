"""Export a .docx to PDF through Word COM."""

import os
import sys

import win32com.client

PDF = 17


def main():
    source = os.path.abspath(sys.argv[1])
    target = os.path.abspath(sys.argv[2])
    print("source=%s exists=%s" % (source, os.path.isfile(source)))
    app = win32com.client.DispatchEx("Word.Application")
    app.Visible = False
    app.DisplayAlerts = 0
    print("version=%s" % app.Version)
    try:
        doc = app.Documents.Open(source, False, True, False)
        print("pages=%d tables=%d" % (doc.ComputeStatistics(2), doc.Tables.Count))
        if os.path.isfile(target):
            os.remove(target)
        doc.ExportAsFixedFormat(target, PDF)
        print("pdf exists=%s size=%s" % (os.path.isfile(target),
                                         os.path.getsize(target) if os.path.isfile(target) else -1))
        doc.Close(0)
    finally:
        app.Quit()
        print("quit")


main()
