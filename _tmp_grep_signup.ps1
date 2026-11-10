$root = 'c:\xampp\htdocs\shopping'
$patterns = 'country|seller_status|id_document|business_name|tin\b|rdb'
Get-ChildItem -LiteralPath $root -Recurse -Include *.php -File |
  Where-Object { $_.FullName -notlike '*\src\*' -and $_.FullName -notlike '*node_modules*' } |
  ForEach-Object {
    $rel = $_.FullName.Replace($root + '\', '')
    $hits = Select-String -LiteralPath $_.FullName -Pattern $patterns -AllMatches -ErrorAction SilentlyContinue
    foreach ($h in $hits) {
      Write-Output ($rel + ':' + $h.LineNumber + ': ' + $h.Line.Trim())
    }
  }
