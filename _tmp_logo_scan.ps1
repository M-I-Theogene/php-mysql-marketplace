Add-Type -AssemblyName System.Drawing
$root = 'c:\xampp\htdocs\shopping'
$files = Get-ChildItem -LiteralPath $root -Recurse -Include *.png,*.jpg,*.jpeg,*.gif -File -ErrorAction SilentlyContinue |
  Where-Object { $_.FullName -notlike '*\src\images\products\*' -and $_.FullName -notlike '*\src\images\kyc\*' }
foreach ($f in $files) {
  try {
    $i = [System.Drawing.Image]::FromFile($f.FullName)
    $rel = $f.FullName.Replace($root + '\', '')
    Write-Output ($rel + '  w=' + $i.Width + '  h=' + $i.Height + '  ' + [math]::Round($f.Length/1KB,1) + ' KB')
    $i.Dispose()
  } catch {
    Write-Output ($f.FullName.Replace($root + '\', '') + '  [unreadable]')
  }
}
