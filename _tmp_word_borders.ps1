$ErrorActionPreference = 'Stop'
$path = 'C:\xampp\htdocs\shopping\pawapay-documents\Cypadi Ltd - KYC and AML Policy - CYP-AML-001 v1.1.docx'
Get-Process WINWORD -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2
$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0
$doc = $word.Documents.Open($path, $false, $true, $false)
$count = 0
foreach ($paragraph in $doc.Paragraphs) {
  $count++
  if ($count -gt 8) { break }
  $b = $paragraph.Format.Borders.Item(-3)
  $text = $paragraph.Range.Text
  if ($text.Length -gt 40) { $text = $text.Substring(0, 40) }
  $text = $text -replace "[\r\n\a]", ' '
  Write-Output ("para " + $count + " bottomLineStyle=" + $b.LineStyle + " color=" + $b.ForeColor + " text={" + $text + "}")
}
foreach ($section in $doc.Sections) {
  $header = $section.Headers.Item(1)
  $hp = $header.Range.Paragraphs.Item(1)
  $hb = $hp.Format.Borders.Item(-3)
  Write-Output ("header bottomLineStyle=" + $hb.LineStyle + " color=" + $hb.ForeColor)
}
$doc.Close(0)
$word.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null
[GC]::Collect()
Write-Output 'DONE'
