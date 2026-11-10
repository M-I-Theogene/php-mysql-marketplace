$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing

$root       = 'c:\xampp\htdocs\shopping'
$logo       = Join-Path $root 'assets\images\cypadi-logo-transparent.png'
$headerLogo = Join-Path $root '_tmp_logo_header.png'
$outDir     = Join-Path $root 'pawapay-documents'

if (-not (Test-Path $outDir)) { New-Item -ItemType Directory -Path $outDir | Out-Null }

# ---- pre-size the logo so Word places it at ~74 pt wide without a resize call ----
$TARGET_WIDTH_PX = 308          # 74 pt at 300 dpi
$sourceLogo = [System.Drawing.Image]::FromFile($logo)
$ratio      = $TARGET_WIDTH_PX / $sourceLogo.Width
$targetH    = [int][Math]::Round($sourceLogo.Height * $ratio)
$canvas     = New-Object System.Drawing.Bitmap($TARGET_WIDTH_PX, $targetH)
$graphics   = [System.Drawing.Graphics]::FromImage($canvas)
$graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
$graphics.PixelOffsetMode   = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
$graphics.Clear([System.Drawing.Color]::Transparent)
$graphics.DrawImage($sourceLogo, 0, 0, $TARGET_WIDTH_PX, $targetH)
$graphics.Dispose()
$canvas.SetResolution(300, 300)
$canvas.Save($headerLogo, [System.Drawing.Imaging.ImageFormat]::Png)
$canvas.Dispose()
$sourceLogo.Dispose()
Write-Output ('LOGO_HEADER_BUILT ' + $TARGET_WIDTH_PX + 'x' + $targetH + ' @300dpi')

$jobs = @(
  @{
    Html  = '_tmp_doc1_kyc.html'
    Base  = 'Cypadi Ltd - KYC and AML Policy - CYP-AML-001 v1.1'
    Ref   = 'CYP-AML-001'
    Title = 'KYC and Anti-Money Laundering Policy'
  },
  @{
    Html  = '_tmp_doc2_vdd.html'
    Base  = 'Cypadi Ltd - Vendor Due Diligence Procedures - CYP-VDD-001 v1.1'
    Ref   = 'CYP-VDD-001'
    Title = 'Vendor Due Diligence Procedures'
  }
)

$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0
$word.ScreenUpdating = $false
$word.Options.SaveInterval = 0
$word.Options.WarnBeforeSavingPrintingSendingMarkup = $false

foreach ($job in $jobs) {
  $htmlPath = Join-Path $root $job.Html
  Write-Output ('--- building ' + $job.Base + ' ---')

  $doc = $word.Documents.Open($htmlPath, $false, $false, $false)

  $doc.PageSetup.PaperSize      = 9
  $doc.PageSetup.TopMargin      = 76
  $doc.PageSetup.BottomMargin   = 62
  $doc.PageSetup.LeftMargin     = 60
  $doc.PageSetup.RightMargin    = 60
  $doc.PageSetup.HeaderDistance = 30
  $doc.PageSetup.FooterDistance = 30

  $contentWidth = $doc.PageSetup.PageWidth - $doc.PageSetup.LeftMargin - $doc.PageSetup.RightMargin

  # ---------- header ----------
  $hdr = $doc.Sections.Item(1).Headers.Item(1)
  $hdr.LinkToPrevious = $false
  $hdr.Range.Text = ''
  $hdr.Range.ParagraphFormat.Alignment = 0
  $hdr.Range.ParagraphFormat.TabStops.ClearAll()
  $hdr.Range.ParagraphFormat.TabStops.Add($contentWidth, 2, 0) | Out-Null
  $hdr.Range.ParagraphFormat.SpaceAfter = 2

  $hdr.Range.Collapse(1) | Out-Null
  $hdr.Range.InlineShapes.AddPicture($headerLogo, $false, $true) | Out-Null
  Write-Output ('   header images=' + $hdr.Range.InlineShapes.Count)

  $tail = $hdr.Range
  $tail.Collapse(0) | Out-Null
  $tail.InsertAfter("`tCypadi Ltd  |  " + $job.Ref + "  |  Version 1.1")
  $tail.Font.Size = 8
  $tail.Font.Color = 8421504
  $tail.Font.Name = 'Calibri'

  $hdr.Range.ParagraphFormat.Borders.Item(-3).LineStyle = 1
  $hdr.Range.ParagraphFormat.Borders.Item(-3).LineWidth = 12
  $hdr.Range.ParagraphFormat.Borders.Item(-3).Color = 5510840

  # ---------- footer ----------
  $ftr = $doc.Sections.Item(1).Footers.Item(1)
  $ftr.LinkToPrevious = $false
  $ftr.Range.Text = ''
  $ftr.Range.ParagraphFormat.Alignment = 0
  $ftr.Range.ParagraphFormat.TabStops.ClearAll()
  $ftr.Range.ParagraphFormat.TabStops.Add($contentWidth, 2, 0) | Out-Null

  $ftr.Range.Collapse(1) | Out-Null
  $ftr.Range.InsertAfter('Cypadi Ltd  |  ' + $job.Title + '  |  Internal - Confidential' + "`t" + 'Page ')
  $ftr.Range.Font.Size = 8
  $ftr.Range.Font.Color = 8421504
  $ftr.Range.Font.Name = 'Calibri'

  $pageRange = $ftr.Range
  $pageRange.Collapse(0) | Out-Null
  $ftr.Range.Fields.Add($pageRange, 33, $null, $false) | Out-Null

  $midRange = $ftr.Range
  $midRange.Collapse(0) | Out-Null
  $ftr.Range.InsertAfter(' of ')
  $ftr.Range.Font.Size = 8
  $ftr.Range.Font.Color = 8421504

  $totalRange = $ftr.Range
  $totalRange.Collapse(0) | Out-Null
  $ftr.Range.Fields.Add($totalRange, 26, $null, $false) | Out-Null

  # ---------- properties ----------
  $doc.BuiltInDocumentProperties.Item('Title').Value    = $job.Title
  $doc.BuiltInDocumentProperties.Item('Author').Value   = 'Cypadi Ltd'
  $doc.BuiltInDocumentProperties.Item('Company').Value  = 'Cypadi Ltd'
  $doc.BuiltInDocumentProperties.Item('Subject').Value  = 'PawaPay due diligence submission'
  $doc.BuiltInDocumentProperties.Item('Comments').Value = 'Cypadi Ltd - ' + $job.Ref + ' - Version 1.1'

  # ---------- statistics ----------
  $doc.Repaginate()
  $pages  = $doc.ComputeStatistics(2)
  $words  = $doc.ComputeStatistics(0)
  $tables = $doc.Tables.Count
  Write-Output ('   pages=' + $pages + ' words=' + $words + ' tables=' + $tables)

  # ---------- save ----------
  $docxPath = Join-Path $outDir ($job.Base + '.docx')
  $pdfPath  = Join-Path $outDir ($job.Base + '.pdf')

  if (Test-Path $docxPath) { Remove-Item $docxPath -Force }
  if (Test-Path $pdfPath)  { Remove-Item $pdfPath -Force }

  $doc.SaveAs2($docxPath, 12)
  $doc.SaveAs2($pdfPath, 17)
  $doc.Close(0)

  Write-Output ('   saved: ' + $job.Base + '.docx')
  Write-Output ('   saved: ' + $job.Base + '.pdf')
}

$word.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null
Write-Output 'BUILD_DONE'
