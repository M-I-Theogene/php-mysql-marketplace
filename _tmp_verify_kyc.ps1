$ErrorActionPreference = 'Continue'
$base = 'http://127.0.0.1:8099'
$kycDir = 'C:\xampp\htdocs\shopping\src\images\kyc'
$mysql = 'C:\xampp\mysql\bin\mysql.exe'

Copy-Item 'C:\xampp\htdocs\shopping\src\images\products\0b157c413d7f06b752b4992508f556f4.jpg' (Join-Path $kycDir 'kyc_probe_test.jpg') -Force

$adminId = (& $mysql -u root marketplace -N -e "SELECT id FROM accounts WHERE role='admin' ORDER BY id LIMIT 1;" | Out-String).Trim()
Write-Output "adminId=$adminId"

function Get-Probe {
    param($Url, $Session, $Label, $Needle)

    try {
        $r = Invoke-WebRequest -Uri $Url -UseBasicParsing -WebSession $Session -TimeoutSec 20
        $ct = $r.Headers['Content-Type']
        Write-Output ("{0} => HTTP {1} | type={2} | bytes={3}" -f $Label, $r.StatusCode, $ct, $r.RawContentLength)
        if ($Needle) {
            if ($r.Content -match $Needle) { Write-Output "    body contains: $Needle" }
            else { Write-Output "    MISSING from body: $Needle" }
        }
    } catch {
        $resp = $_.Exception.Response
        $code = if ($resp) { [int]$resp.StatusCode } else { 'ERR' }
        $body = if ($_.ErrorDetails) { $_.ErrorDetails.Message } else { '' }
        Write-Output ("{0} => HTTP {1}" -f $Label, $code)
        if ($Needle) {
            if ($body -match $Needle) { Write-Output "    body contains: $Needle" }
            else { Write-Output "    MISSING from body: $Needle" }
        }
    }
}

$anonymous = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$adminSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$sellerSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession

$null = Invoke-WebRequest -Uri "$base/_tmp_session_seed.php?role=admin&id=$adminId" -UseBasicParsing -WebSession $adminSession
$null = Invoke-WebRequest -Uri "$base/_tmp_session_seed.php?role=seller&id=14" -UseBasicParsing -WebSession $sellerSession

Get-Probe "$base/kyc-document.php?file=kyc_probe_test.jpg" $anonymous 'A anonymous' 'Sign in required'
Get-Probe "$base/kyc-document.php?file=kyc_probe_test.jpg" $adminSession 'B admin existing file'
Get-Probe "$base/kyc-document.php?file=kyc_70bc0b6e6dfdc9df3460042f149572a6.jpg" $adminSession 'C admin missing file' 'document file is missing'
Get-Probe "$base/kyc-document.php?file=..%2Fconfig.php" $adminSession 'D traversal attempt'
Get-Probe "$base/kyc-document.php?file=kyc_probe_test.jpg" $sellerSession 'E seller1

[Response interrupted by a tool use result. Only one tool may be used at a time and should be placed at the end of the message.]
