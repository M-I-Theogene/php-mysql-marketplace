$urls = @(
    'http://localhost/shopping/src/images/kyc/kyc_70bc0b6e6dfdc9df3460042f149572a6.jpg',
    'http://localhost/shopping/admin/sellers.php'
)

foreach ($u in $urls) {
    try {
        $r = Invoke-WebRequest -Uri $u -UseBasicParsing -TimeoutSec 10
        Write-Output ("{0} => {1}" -f $u, $r.StatusCode)
    } catch {
        $code = 'ERR'
        if ($_.Exception.Response) { $code = [int]$_.Exception.Response.StatusCode }
        Write-Output ("{0} => {1} ({2})" -f $u, $code, $_.Exception.Message)
    }
}
