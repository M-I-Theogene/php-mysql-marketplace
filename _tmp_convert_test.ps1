$ErrorActionPreference = 'Stop'
$root = 'c:\xampp\htdocs\shopping'
$docx = Join-Path $root '_tmp_border_test.docx'
$pdf  = Join-Path $root '_tmp_border_test.pdf'
Get-Process WINWORD -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2
$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0
$doc = $word.Documents.Open($docx, $false, $true, $false)
if (Test-Path $pdf) { Remove-Item $pdf -Force }
$doc.ExportAsFixedFormat($pdf, 17)
$doc.Close(0)
$word.Quit()
Write-Output 'CONVERTED'
