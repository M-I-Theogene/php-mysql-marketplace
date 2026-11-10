$ErrorActionPreference = 'Stop'
$root = 'C:\xampp\htdocs\shopping'
$tmp = Join-Path $root '_tmp_rootpdf'
if (Test-Path $tmp) { Remove-Item $tmp -Recurse -Force }
New-Item -ItemType Directory -Path $tmp | Out-Null

Get-Process WINWORD -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2

$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0

$targets = @(
  'KYC and AML Policy (Final).docx',
  'Vendor Due Diligence Procedures (Final).docx'
)

foreach ($name in $targets) {
  $path = Join-Path $root $name
  if (-not (Test-Path $path)) { Write-Output ('MISSING ' + $name); continue }
  $doc = $word.Documents.Open($path, $false, $true, $false)
  $doc.Fields.Update() | Out-Null
  $doc.Repaginate()
  $pages = $doc.ComputeStatistics(2)
  $out = Join-Path $tmp ([System.IO.Path]::GetFileNameWithoutExtension($name) + '.pdf')
  $doc.ExportAsFixedFormat($out, 17)
  $doc.Close(0)
  Write-Output ($name + ' pages=' + $pages)
}

$word.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null
[GC]::Collect()
Write-Output 'ROOT_DONE'
