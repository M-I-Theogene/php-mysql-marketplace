$ErrorActionPreference = 'SilentlyContinue'
$url = 'http://localhost/shopping/pawapay-documents/_preview/kyc-p1.png'
try {
  $response = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 10
  Write-Output ('HTTP_OK status=' + $response.StatusCode + ' bytes=' + $response.RawContentLength)
} catch {
  Write-Output ('HTTP_FAIL ' + $_.Exception.Message)
}
