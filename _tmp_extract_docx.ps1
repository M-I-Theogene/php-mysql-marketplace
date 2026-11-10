Add-Type -AssemblyName System.IO.Compression.FileSystem
$targets = @(
  'c:\xampp\htdocs\shopping\KYC and AML Policy (Final).docx',
  'c:\xampp\htdocs\shopping\Vendor Due Diligence Procedures (Final).docx'
)
foreach ($t in $targets) {
  Write-Output ('################ ' + (Split-Path $t -Leaf) + ' ################')
  $z = [System.IO.Compression.ZipFile]::OpenRead($t)
  $entry = $z.Entries | Where-Object { $_.FullName -eq 'word/document.xml' }
  $sr = New-Object System.IO.StreamReader($entry.Open())
  $xml = $sr.ReadToEnd()
  $sr.Close()
  $z.Dispose()
  $xml = $xml -replace '</w:p>', "`n"
  $xml = $xml -replace '</w:tr>', "`n"
  $xml = $xml -replace '</w:tc>', ' | '
  $xml = $xml -replace '<w:tab[^>]*/>', "`t"
  $xml = $xml -replace '<w:br[^>]*/>', "`n"
  $xml = $xml -replace '<[^>]+>', ''
  Write-Output $xml
  Write-Output ''
}
