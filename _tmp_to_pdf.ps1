$ErrorActionPreference = 'Stop'
$root   = 'c:\xampp\htdocs\shopping'
$outDir = Join-Path $root 'pawapay-documents'

Get-Process WINWORD -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2

$files = Get-ChildItem -Path $outDir -Filter '*.docx' -File
if (-not $files) { throw 'No .docx files found in pawapay-documents' }

$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0
$word.ScreenUpdating = $false

foreach ($file in $files) {
  Write-Output ('--- converting ' + $file.Name + ' ---')
  $doc = $word.Documents.Open($file.FullName, $false, $true, $false)

  $doc.Fields.Update() | Out-Null
  foreach ($section in $doc.Sections) {
    foreach ($header in $section.Headers) { $header.Range.Fields.Update() | Out-Null }
    foreach ($footer in $section.Footers) { $footer.Range.Fields.Update() | Out-Null }
  }
  $doc.Repaginate()

  $pages  = $doc.ComputeStatistics(2)
  $words  = $doc.ComputeStatistics(0)
  $tables = $doc.Tables.Count

  $headerShapes = 0
  foreach ($section in $doc.Sections) {
    foreach ($header in $section.Headers) {
      $headerShapes += $header.Range.InlineShapes.Count
    }
  }
  $logoWidth = 0
  foreach ($section in $doc.Sections) {
    foreach ($header in $section.Headers) {
      if ($header.Range.InlineShapes.Count -gt 0) {
        $logoWidth = [math]::Round($header.Range.InlineShapes.Item(1).Width, 1)
      }
    }
  }

  Write-Output ('   pages=' + $pages + ' words=' + $words + ' tables=' + $tables)
  Write-Output ('   header_images=' + $headerShapes + ' logo_width_pt=' + $logoWidth)

  foreach ($section in $doc.Sections) {
    $footerText = $section.Footers.Item(1).Range.Text
    Write-Output ('   footer=' + ($footerText -replace "[\r\n\a]", ' ').Trim())
  }

  $pdfPath = [System.IO.Path]::ChangeExtension($file.FullName, '.pdf')
  if (Test-Path $pdfPath) { Remove-Item $pdfPath -Force }
  $doc.ExportAsFixedFormat($pdfPath, 17)
  $doc.Close(0)
  Write-Output ('   saved ' + (Split-Path $pdfPath -Leaf))
}

$word.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null
[GC]::Collect()
Write-Output 'PDF_DONE'
