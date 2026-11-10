$ErrorActionPreference = 'Stop'
$root = 'c:\xampp\htdocs\shopping'
$source = Join-Path $root '_tmp_probe_border.docx'
$target = Join-Path $root '_tmp_probe_border.pdf'

if (-not (Test-Path $source)) { throw 'probe docx missing' }
if (Test-Path $target) { Remove-Item $target -Force }

$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0
$word.ScreenUpdating = $false

$doc = $word.Documents.Open($source, $false, $true, $false)
$doc.ExportAsFixedFormat($target, 17)
Write-Output ('pages=' + $doc.ComputeStatistics(2))
$doc.Close(0)
$word.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null
[GC]::Collect()
Write-Output 'PROBE_CONVERTED'
