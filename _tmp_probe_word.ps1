$ErrorActionPreference = 'Stop'
$root = 'c:\xampp\htdocs\shopping'
$logo = Join-Path $root 'assets\images\cypadi-logo-transparent.png'
$outDir = Join-Path $root 'pawapay-documents'

Write-Output ('LOGO_EXISTS=' + (Test-Path $logo))
if (Test-Path $logo) {
  $fi = Get-Item $logo
  Write-Output ('LOGO_BYTES=' + $fi.Length)
}

Write-Output ('OUTDIR_EXISTS=' + (Test-Path $outDir))
if (Test-Path $outDir) {
  Get-ChildItem $outDir | ForEach-Object { Write-Output ('OUTDIR_ITEM=' + $_.Name + ' ' + $_.Length) }
}

try {
  $word = New-Object -ComObject Word.Application
  $word.Visible = $false
  Write-Output ('WORD_VERSION=' + $word.Version)
  $word.Quit()
  [System.Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null
} catch {
  Write-Output ('WORD_MISSING=' + $_.Exception.Message)
}

Write-Output 'PROBE_DONE'
