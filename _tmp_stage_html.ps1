$ErrorActionPreference = 'Stop'

$root   = 'c:\xampp\htdocs\shopping'
$logo   = Join-Path $root 'assets\images\cypadi-logo-transparent.png'
$outDir = Join-Path $root 'pawapay-documents'
if (-not (Test-Path $outDir)) { New-Item -ItemType Directory -Path $outDir | Out-Null }

$jobs = @(
  @{ Src = '_tmp_doc1_kyc.txt'; Html = '_tmp_doc1_kyc.html'; Base = 'Cypadi Ltd - KYC and AML Policy - v1.1'; Ref = 'CYP-AML-001'; Title = 'KYC and Anti-Money Laundering Policy' },
  @{ Src = '_tmp_doc2_vdd.txt'; Html = '_tmp_doc2_vdd.html'; Base = 'Cypadi Ltd - Vendor Due Diligence Procedures - v1.1'; Ref = 'CYP-VDD-001'; Title = 'Vendor Due Diligence Procedures' }
)

foreach ($job in $jobs) {
  $srcPath  = Join-Path $root $job.Src
  $htmlPath = Join-Path $root $job.Html
  Copy-Item -LiteralPath $srcPath -Destination $htmlPath -Force
  Write-Output ('copied ' + $job.Src + ' -> ' + $job.Html)
}

Write-Output ('html present: ' + (Test-Path (Join-Path $root '_tmp_doc1_kyc.html')))
Write-Output ('logo present: ' + (Test-Path $logo))
