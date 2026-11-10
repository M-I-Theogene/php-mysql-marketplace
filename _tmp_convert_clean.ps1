$ErrorActionPreference = 'Stop'
$outDir = 'C:\xampp\htdocs\shopping\pawapay-documents'
Get-Process WINWORD -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2

$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0

foreach ($file in (Get-ChildItem -Path $outDir -Filter '*.docx' -File)) {
  $doc = $word.Documents.Open($file.FullName, $false, $true, $false)
  $doc.Fields.Update() | Out-Null
  $doc.Repaginate()

  $pages = $doc.ComputeStatistics(2)
  $logoWidth = 0
  foreach ($section in $doc.Sections) {
    if ($section.Headers.Item(1).Range.InlineShapes.Count -gt 0) {
      $logoWidth = [math]::Round($section.Headers.Item(1).Range.InlineShapes.Item(1).Width, 1)
    }
  }
  $footerText = ($doc.Sections.Item(1).Footers.Item(1).Range.Text -replace "[\r\n\a]", ' ').Trim()

  $pdfPath = [System.IO.Path]::ChangeExtension($file.FullName, '.pdf')
  if (Test-Path $pdfPath) { Remove-Item $pdfPath -Force }
  $doc.ExportAsFixedFormat($pdfPath, 17)
  $doc.Close(0)

  Write-Output ('   pages=' + $pages + ' logo_width_pt=' + $logoWidth)
  Write-Output ('   footer=' + $footerText)
  Write-Output ('   saved ' + (Split-Path $pdfPath -Leaf))
}

$word.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null
[GC]::Collect()
Write-Output 'PDF_DONE'
