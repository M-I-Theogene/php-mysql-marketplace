$ErrorActionPreference = 'Stop'
$path = 'C:\xampp\htdocs\shopping\pawapay-documents\Cypadi Ltd - KYC and AML Policy - CYP-AML-001 v1.1.docx'
$out  = 'C:\xampp\htdocs\shopping\_tmp_reexport.pdf'
Get-Process WINWORD -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2
if (Test-Path $out) { Remove-Item $out -Force }
$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0
$doc = $word.Documents.Open($path, $false, $true, $false)
$doc.Fields.Update() | Out-Null
$doc.Repaginate()
$doc.ExportAsFixedFormat($out, 17)
$doc.Close(0)
$word.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null
[GC]::Collect()
Write-Output ('SAVED ' + $out)
