$ErrorActionPreference = 'Stop'
Get-Process WINWORD -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2
$path = 'C:\xampp\htdocs\shopping\_tmp_isolate.docx'
$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0
$doc = $word.Documents.Open($path, $false, $true, $false)
$doc.Fields.Update() | Out-Null
$doc.Repaginate()
$pdf = [System.IO.Path]::ChangeExtension($path, '.pdf')
if (Test-Path $pdf) { Remove-Item $pdf -Force }
$doc.ExportAsFixedFormat($pdf, 17)
$doc.Close(0)
$word.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null
[GC]::Collect()
Write-Output ('SAVED ' + $pdf)
