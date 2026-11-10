Add-Type -AssemblyName System.IO.Compression.FileSystem
$files = Get-ChildItem -LiteralPath 'c:\xampp\htdocs\shopping' -Filter '*.docx'
foreach ($f in $files) {
  Write-Output ('===== ' + $f.Name + '  (' + [math]::Round($f.Length/1KB,1) + ' KB) =====')
  $z = [System.IO.Compression.ZipFile]::OpenRead($f.FullName)
  foreach ($e in $z.Entries) {
    Write-Output ('   ' + $e.FullName + '   ' + $e.Length)
  }
  $z.Dispose()
  Write-Output ''
}
