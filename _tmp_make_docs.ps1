$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing

$root   = 'c:\xampp\htdocs\shopping'
$logo   = Join-Path $root 'assets\images\cypadi-logo-transparent.png'
$outDir = Join-Path $root 'pawapay-documents'
if (-not (Test-Path $outDir)) { New-Item -ItemType Directory -Path $outDir | Out-Null }

Write-Output '--- fetching official logo ---'
try {
  Invoke-WebRequest -Uri 'https://cypadi.com/logo/cypadi_trancyparent_logo.png' -OutFile $logo -UseBasicParsing -TimeoutSec 45
  Write-Output 'LOGO_DOWNLOAD_OK'
} catch {
  Write-Output ('LOGO_DOWNLOAD_FAILED: ' + $_.Exception.Message)
}
$img = [System.Drawing.Image]::FromFile($logo)
Write-Output ('LOGO = ' + $img.Width + 'x' + $img.Height)
$img.Dispose()

$head = @'
<html><head><meta charset="utf-8"><style>
body{font-family:Calibri,Carlito,sans-serif;font-size:10.5pt;color:#1B2A38;line-height:1.45;}
p{margin:0 0 7pt 0;text-align:justify;}
h1{font-size:20pt;font-weight:bold;color:#1B2A38;margin:0 0 2pt 0;}
.subtitle{font-size:10pt;color:#8A6320;letter-spacing:1.5pt;text-transform:uppercase;margin:0 0 14pt 0;}
.goldrule{border-bottom:3pt solid #D8A830;margin:0 0 16pt 0;height:1pt;}
h2{font-size:12.5pt;font-weight:bold;color:#1B2A38;margin:16pt 0 6pt 0;border-bottom:0.75pt solid #D8A830;padding-bottom:3pt;}
h3{font-size:10.5pt;font-weight:bold;color:#8A6320;margin:11pt 0 4pt 0;}
ul{margin:0 0 8pt 0;padding-left:18pt;} li{margin-bottom:3pt;text-align:justify;}
table{border-collapse:collapse;width:100%;margin:0 0 12pt 0;font-size:9.5pt;}
th{background:#1B2A38;color:#FFFFFF;text-align:left;padding:5pt 7pt;font-weight:bold;border:0.5pt solid #1B2A38;}
td{padding:5pt 7pt;border:0.5pt solid #C9C2B4;vertical-align:top;}
tr.alt td{background:#FAF7F0;}
.lbl{width:32%;font-weight:bold;background:#F3EEE3;}
.ph{color:#B3261E;font-weight:bold;}
.sigline{border-bottom:0.75pt solid #1B2A38;height:26pt;width:78%;}
.stampbox{border:1pt dashed #9A9184;height:118pt;width:130pt;text-align:center;color:#9A9184;font-size:8.5pt;letter-spacing:1pt;}
.small{font-size:9pt;color:#5A5348;}
</style></head><body>
'@

$sig = @'
<h2>Approval</h2>
<p>Approved by the Managing Director of Cypadi Ltd on the date shown below, with effect from that date.</p>
<table>
<tr>
<td style="width:52%;border:none;padding-top:14pt;">
<p class="small" style="margin-bottom:2pt;"><strong>SIGNED FOR AND ON BEHALF OF CYPADI LTD</strong></p>
<div class="sigline"></div>
<p style="margin:6pt 0 0 0;text-align:left;"><strong><span class="ph">[[MD_NAME]]</span></strong><br>Managing Director, Cypadi Ltd</p>
<p style="margin:10pt 0 0 0;text-align:left;">Signature: ______________________________</p>
<p style="margin:10pt 0 0 0;text-align:left;">Date: <span class="ph">[[SIGN_DATE]]</span></p>
</td>
<td style="border:none;vertical-align:top;padding-top:14pt;">
<div class="stampbox">COMPANY STAMP</div>
</td>
</tr>
</table>
<p class="small">Cypadi Ltd &middot; Kigali, Rwanda &middot; info@cypadi.com &middot; compliance@cypadi.com &middot; shop.cypadi.com</p>
'@

$controlKyc = @'
<h2>Document control</h2>
<table>
<tr><td class="lbl">Document title</td><td>KYC and Anti-Money Laundering Policy</td></tr>
<tr class="alt"><td class="lbl">Organisation</td><td>CYPADI LTD, a private limited company incorporated in Rwanda</td></tr>
<tr><td class="lbl">RDB registration number</td><td><span class="ph">[[RDB_NUMBER]]</span></td></tr>
<tr class="alt"><td class="lbl">Tax Identification Number (TIN)</td><td><span class="ph">[[TIN]]</span></td></tr>
<tr><td class="lbl">Registered address</td><td><span class="ph">[[REGISTERED_ADDRESS]]</span>, Kigali, Rwanda</td></tr>
<tr class="alt"><td class="lbl">Business activity</td><td>Operation of the online retail marketplace shop.cypadi.com</td></tr>
<tr><td class="lbl">Document reference and version</td><td>CYP-AML-001 &middot; Version 1.1</td></tr>
<tr class="alt"><td class="lbl">Effective date</td><td><span class="ph">[[EFFECTIVE_DATE]]</span></td></tr>
<tr><td class="lbl">Policy owner</td><td><span class="ph">[[COMPLIANCE_OFFICER]]</span>, Compliance Officer &mdash; compliance@cypadi.com, <span class="ph">[[CO_PHONE]]</span></td></tr>
<tr class="alt"><td class="lbl">Approved by</td><td><span class="ph">[[MD_NAME]]</span>, Managing Director</td></tr>
<tr><td class="lbl">Next scheduled review</td><td>Twelve (12) months from the effective date, and on any material change</td></tr>
<tr class="alt"><td class="lbl">Classification</td><td>Internal &mdash; may be shared with payment partners, banks and competent authorities</td></tr>
</table>
<h3>Version history</h3>
<table>
<tr><th style="width:12%">Version</th><th style="width:20%">Date</th><th>Summary of change</th></tr>
<tr><td>1.0</td><td>July 2026</td><td>Initial issue.</td></tr>
<tr class="alt"><td>1.1</td><td><span class="ph">[[EFFECTIVE_DATE]]</span></td><td>Document control and governance added; named Compliance Officer; customer and enhanced due diligence thresholds defined; sanctions, PEP and adverse media screening added; suspicious transaction reporting to the Financial Intelligence Centre added; five-year retention set; risk rating, refusal, training and review sections added.</td></tr>
</table>
'@

$controlVdd = @'
<h2>Document control</h2>
<table>
<tr><td class="lbl">Document title</td><td>Vendor Due Diligence Procedures</td></tr>
<tr class="alt"><td class="lbl">Organisation</td><td>CYPADI LTD, a private limited company incorporated in Rwanda</td></tr>
<tr><td class="lbl">RDB registration number</td><td><span class="ph">[[RDB_NUMBER]]</span></td></tr>
<tr class="alt"><td class="lbl">Tax Identification Number (TIN)</td><td><span class="ph">[[TIN]]</span></td></tr>
<tr><td class="lbl">Registered address</td><td><span class="ph">[[REGISTERED_ADDRESS]]</span>, Kigali, Rwanda</td></tr>
<tr class="alt"><td class="lbl">Business activity</td><td>Operation of the online retail marketplace shop.cypadi.com</td></tr>
<tr><td class="lbl">Document reference and version</td><td>CYP-VDD-001 &middot; Version 1.1</td></tr>
<tr class="alt"><td class="lbl">Effective date</td><td><span class="ph">[[EFFECTIVE_DATE]]</span></td></tr>
<tr><td class="lbl">Procedure owner</td><td><span class="ph">[[COMPLIANCE_OFFICER]]</span>, Compliance Officer &mdash; compliance@cypadi.com, <span class="ph">[[CO_PHONE]]</span></td></tr>
<tr class="alt"><td class="lbl">Approved by</td><td><span class="ph">[[MD_NAME]]</span>, Managing Director</td></tr>
<tr><td class="lbl">Next scheduled review</td><td>Twelve (12) months from the effective date, and on any material change</td></tr>
<tr class="alt"><td class="lbl">Related documents</td><td>KYC and Anti-Money Laundering Policy (CYP-AML-001); Vendor Agreement; Privacy Policy</td></tr>
<tr><td class="lbl">Classification</td><td>Internal &mdash; may be shared with payment partners, banks and competent authorities</td></tr>
</table>
<h3>Version history</h3>
<table>
<tr><th style="width:12%">Version</th><th style="width:20%">Date</th><th>Summary of change</th></tr>
<tr><td>1.0</td><td>July 2026</td><td>Initial issue.</td></tr>
<tr class="alt"><td>1.1</td><td><span class="ph">[[EFFECTIVE_DATE]]</span></td><td>Document control added; approval gate and decision record defined; identity and business verification standards set; prohibited product list added; vendor risk rating, screening, ongoing monitoring and five-year retention added; settlement, refusal, training and review sections added.</td></tr>
</table>
'@

Write-Output 'part 1 loaded'
