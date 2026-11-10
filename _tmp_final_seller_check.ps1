$base = 'http://localhost:81/shopping'
$curl = 'C:\Windows\System32\curl.exe'
$cookies = Join-Path $PSScriptRoot '_tmp_c2.txt'

& $curl -s -c $cookies -d 'username=admin&password=123&submit=1' -o NUL "$base/login/"
& $curl -s -b $cookies -o (Join-Path $PSScriptRoot '_tmp_s2.html') "$base/admin/sellers.php"

$html = Get-Content (Join-Path $PSScriptRoot '_tmp_s2.html') -Raw

foreach ($pattern in @('Warning:', 'Notice:', 'Fatal error', 'Undefined variable', 'Undefined array key', 'Deprecated:', 'in C:\xampp')) {
    if ($html.Contains($pattern)) {
        Write-Output ('STILL PRESENT: ' + $pattern)
    }
}
Write-Output 'php diagnostics scan complete'

Write-Output ('sidebar Sellers entry: ' + $html.Contains('href="./sellers.php"'))
Write-Output ('review card: ' + $html.Contains('Awaiting review'))
Write-Output ('ID document column: ' + $html.Contains('ID document'))
Write-Output ('dashboard still loads: ')
& $curl -s -b $cookies -o NUL -w '%{http_code}' "$base/admin/"
Write-Output ''
