$ErrorActionPreference = 'SilentlyContinue'
$root = 'c:\xampp\htdocs\shopping'

Get-Process WINWORD | Stop-Process -Force
Start-Sleep -Seconds 3
Get-ChildItem -Path $root -Force -File | Where-Object { $_.Name.StartsWith('~$') } | ForEach-Object {
  Write-Output ('REMOVING_LOCK=' + $_.Name)
  Remove-Item -LiteralPath $_.FullName -Force
}
Write-Output ('WINWORD_COUNT=' + (Get-Process WINWORD | Measure-Object).Count)

$pythonCandidates = @('python', 'py', 'python3')
foreach ($candidate in $pythonCandidates) {
  $cmd = Get-Command $candidate -ErrorAction SilentlyContinue
  if ($cmd) {
    Write-Output ('PYTHON_FOUND=' + $candidate + ' -> ' + $cmd.Source)
    $version = & $candidate --version 2>&1
    Write-Output ('PY_VERSION=' + ($version -join ' '))
    $docx = & $candidate -c "import docx; print('DOCX_OK', docx.__version__ if hasattr(docx,'__version__') else 'unknown')" 2>&1
    Write-Output ('PY_DOCX=' + ($docx -join ' | '))
  } else {
    Write-Output ('PYTHON_MISSING=' + $candidate)
  }
}
Write-Output 'ENV_DONE'
