$ErrorActionPreference = 'SilentlyContinue'
$root = 'c:\xampp\htdocs\shopping'

Get-Process WINWORD | Stop-Process -Force
Start-Sleep -Seconds 3

$locks = Get-ChildItem -Path $root -Force -File | Where-Object { $_.Name.StartsWith('~$') }
if ($locks) {
  foreach ($lock in $locks) {
    Write-Output ('LOCK_FOUND=' + $lock.Name)
    Remove-Item -LiteralPath $lock.FullName -Force
    Write-Output ('LOCK_REMOVED=' + $lock.Name)
  }
} else {
  Write-Output 'NO_LOCKS'
}

$outDir = Join-Path $root 'pawapay-documents'
$outLocks = Get-ChildItem -Path $outDir -Force -File | Where-Object { $_.Name.StartsWith('~$') }
if ($outLocks) {
  foreach ($lock in $outLocks) {
    Write-Output ('OUT_LOCK_FOUND=' + $lock.Name)
    Remove-Item -LiteralPath $lock.FullName -Force
  }
}

Write-Output ('WINWORD_COUNT=' + (Get-Process WINWORD | Measure-Object).Count)
Write-Output 'CLEAN_DONE'
