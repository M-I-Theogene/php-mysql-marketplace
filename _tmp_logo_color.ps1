Add-Type -AssemblyName System.Drawing
$src = 'C:\Users\mboni\Downloads\cypadi_trancyparent_logo.png'
$dest = 'c:\xampp\htdocs\shopping\assets\images\cypadi-logo-transparent.png'
Copy-Item -LiteralPath $src -Destination $dest -Force
$img = [System.Drawing.Image]::FromFile($dest)
Write-Output ('SIZE ' + $img.Width + ' x ' + $img.Height)
$bmp = New-Object System.Drawing.Bitmap($img)
$counts = @{}
for ($y = 0; $y -lt $bmp.Height; $y += 3) {
  for ($x = 0; $x -lt $bmp.Width; $x += 3) {
    $p = $bmp.GetPixel($x, $y)
    if ($p.A -lt 200) { continue }
    $key = '{0},{1},{2}' -f ([int]($p.R / 24) * 24), ([int]($p.G / 24) * 24), ([int]($p.B / 24) * 24)
    if ($counts.ContainsKey($key)) { $counts[$key]++ } else { $counts[$key] = 1 }
  }
}
$bmp.Dispose()
$img.Dispose()
$counts.GetEnumerator() | Sort-Object Value -Descending | Select-Object -First 8 | ForEach-Object {
  Write-Output ('  rgb(' + $_.Key + ')  count=' + $_.Value)
}
