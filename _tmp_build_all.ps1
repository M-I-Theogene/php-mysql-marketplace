$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing

$root   = 'c:\xampp\htdocs\shopping'
$logo   = Join-Path $root 'assets\images\cypadi-logo-transparent.png'
$outDir = Join-Path $root 'pawapay-documents'
if (-not (Test-Path $outDir)) { New-Item -ItemType Directory -Path $outDir | Out-Null }

# ---------- official logo from cypadi.com ----------
$downloaded = $false
try {
  Invoke-WebRequest -Uri 'https://cypadi.com/logo/cypadi_trancyparent_logo.png' -OutFile $logo -UseBasicParsing -TimeoutSec 45
  $downloaded = $true
  Write-Output 'LOGO_DOWNLOAD_OK'
} catch {
  Write-Output ('LOGO_DOWNLOAD_SKIPPED: ' + $_.Exception.Message)
}
$img = [System.Drawing.Image]::FromFile($logo)
Write-Output ('LOGO ' + $img.Width + 'x' + $img.Height + '  downloaded=' + $downloaded)
$img.Dispose()

$css = @'
<style>
  body { font-family: Calibri, Carlito, sans-serif; font-size: 10.5pt; color: #1B2A38; line-height: 1.45; }
  p { margin: 0 0 7pt 0; text-align: justify; }
  h1 { font-size: 20pt; font-weight: bold; color: #1B2A38; margin: 0 0 2pt 0; }
  .subtitle { font-size: 11pt; color: #8A6320; letter-spacing: 1.5pt; text-transform: uppercase; margin: 0 0 14pt 0; }
  .goldrule { border-bottom: 3pt solid #D8A830; margin: 0 0 16pt 0; height: 1pt; }
  h2 { font-size: 12.5pt; font-weight: bold; color: #1B2A38; margin: 18pt 0 6pt 0; border-bottom: 0.75pt solid #D8A830; padding-bottom: 3pt; }
  h3 { font-size: 10.5pt; font-weight: bold; color: #8A6320; margin: 12pt 0 4pt 0; }
  ul { margin: 0 0 8pt 0; padding-left: 18pt; }
  li { margin-bottom: 3pt; text-align: justify; }
  table { border-collapse: collapse; width: 100%; margin: 0 0 12pt 0; font-size: 9.5pt; }
  th { background: #1B2A38; color: #FFFFFF; text-align: left; padding: 5pt 7pt; font-weight: bold; border: 0.5pt solid #1B2A38; }
  td { padding: 5pt 7pt; border: 0.5pt solid #C9C2B4; vertical-align: top; }
  tr.alt td { background: #FAF7F0; }
  .lbl { width: 32%; font-weight: bold; background: #F3EEE3; }
  .ph { color: #B3261E; font-weight: bold; }
  .sigline { border-bottom: 0.75pt solid #1B2A38; height: 26pt; width: 78%; }
  .stampbox { border: 1pt dashed #9A9184; height: 118pt; width: 130pt; text-align: center; color: #9A9184; font-size: 8.5pt; letter-spacing: 1pt; }
  .small { font-size: 9pt; color: #5A5348; }
</style>
'@

$sigBlock = @'
<table>
  <tr>
    <td style="width:52%; border:none; padding-top:18pt;">
      <p class="small" style="margin-bottom:2pt;"><strong>Signed for and on behalf of CYPADI LTD</strong></p>
      <div class="sigline"></div>
      <p style="margin:6pt 0 0 0; text-align:left;"><strong><span class="ph">[[MD_NAME]]</span></strong><br>Managing Director, Cypadi Ltd</p>
      <p style="margin:8pt 0 0 0; text-align:left;">Signature: ______________________________</p>
      <p style="margin:8pt 0 0 0; text-align:left;">Date: <span class="ph">[[SIGN_DATE]]</span></p>
    </td>
    <td style="border:none; vertical-align:top; padding-top:18pt;">
      <div class="stampbox">COMPANY STAMP</div>
    </td>
  </tr>
</table>
'@
$bodyKyc = @'
<h1>KYC and Anti-Money Laundering Policy</h1>
<div class="subtitle">Cypadi Ltd &middot; Document CYP-AML-001</div>
<div class="goldrule"></div>

<h2>Document control</h2>
<table>
  <tr><td class="lbl">Document title</td><td>KYC and Anti-Money Laundering Policy</td></tr>
  <tr class="alt"><td class="lbl">Organisation</td><td>CYPADI LTD</td></tr>
  <tr><td class="lbl">Legal form</td><td>Private limited company incorporated in Rwanda</td></tr>
  <tr class="alt"><td class="lbl">RDB registration number</td><td><span class="ph">[[RDB_NUMBER]]</span></td></tr>
  <tr><td class="lbl">Tax Identification Number (TIN)</td><td><span class="ph">[[TIN]]</span></td></tr>
  <tr class="alt"><td class="lbl">Registered address</td><td><span class="ph">[[REGISTERED_ADDRESS]]</span>, Kigali, Rwanda</td></tr>
  <tr><td class="lbl">Business activity</td><td>Operation of the online retail marketplace shop.cypadi.com</td></tr>
  <tr class="alt"><td class="lbl">Document reference and version</td><td>CYP-AML-001 &middot; Version 1.1</td></tr>
  <tr><td class="lbl">Effective date</td><td><span class="ph">[[EFFECTIVE_DATE]]</span></td></tr>
  <tr class="alt"><td class="lbl">Policy owner</td><td><span class="ph">[[COMPLIANCE_OFFICER]]</span>, Compliance Officer &mdash; compliance@cypadi.com</td></tr>
  <tr><td class="lbl">Approved by</td><td><span class="ph">[[MD_NAME]]</span>, Managing Director</td></tr>
  <tr class="alt"><td class="lbl">Next scheduled review</td><td>Twelve (12) months from the effective date, and on any material change</td></tr>
  <tr><td class="lbl">Classification</td><td>Internal &mdash; may be shared with payment partners, banks and competent authorities</td></tr>
</table>

<h3>Version history</h3>
<table>
  <tr><th style="width:12%">Version</th><th style="width:20%">Date</th><th>Summary of change</th></tr>
  <tr><td>1.0</td><td>July 2026</td><td>Initial issue.</td></tr>
  <tr class="alt"><td>1.1</td><td><span class="ph">[[EFFECTIVE_DATE]]</span></td><td>Document control block, governance and named Compliance Officer added; customer and enhanced due diligence thresholds defined; sanctions, PEP and adverse media screening added; suspicious transaction reporting to the Financial Intelligence Centre added; five-year retention period set; risk, refusal, training and review sections added.</td></tr>
</table>

<h2>1. Purpose and legal basis</h2>
<p>Cypadi Ltd (Cypadi, we) is committed to preventing money laundering, terrorist financing, fraud and the abuse of its marketplace. This policy sets out how Cypadi identifies the parties it deals with, monitors transactions, keeps records and reports suspicion.</p>
<p>This policy is adopted in line with <strong>Law No. 058/2023 of 2023 on the prevention and punishment of money laundering offences</strong>, the directives and guidance of the <strong>Financial Intelligence Centre (FIC)</strong> of Rwanda, and the requirements of the payment institutions and mobile money operators through which Cypadi receives customer payments. Where a partner requirement is stricter than this policy, the stricter requirement applies.</p>

<h2>2. Scope and business model</h2>
<p>Cypadi operates <strong>shop.cypadi.com</strong>, an online marketplace in Rwanda. Independent sellers (vendors) list products on the platform; customers browse, order and pay in Rwandan francs by MTN Mobile Money or Airtel Money. Cypadi reviews each vendor before any product is published and processes settlements to vendors after an order has been completed.</p>
<p>This policy applies to every customer who registers on the platform, every vendor who applies to sell on it, and every employee, contractor and administrator who has access to customer data, orders or payment functions.</p>
<p>Cypadi <strong>does not</strong> operate a mobile money wallet or bank account on behalf of customers, does not accept or hold customer funds outside the settlement account maintained with its payment partner, does not provide money transfer or currency exchange services, and does not lend. Mobile money PINs and wallet credentials are never requested, seen or stored by Cypadi.</p>

<h2>3. Definitions</h2>
<table>
  <tr><th style="width:26%">Term</th><th>Meaning</th></tr>
  <tr><td><strong>CDD</strong></td><td>Customer due diligence: the standard identification information collected from every customer and vendor.</td></tr>
  <tr class="alt"><td><strong>EDD</strong></td><td>Enhanced due diligence: additional verification applied to higher-risk customers, vendors or transactions.</td></tr>
  <tr><td><strong>PEP</strong></td><td>Politically exposed person: a person entrusted with a prominent public function, and their family members and close associates.</td></tr>
  <tr class="alt"><td><strong>MLRO / Compliance Officer</strong></td><td>The person appointed by Cypadi under section 4 to own this policy, assess alerts and file reports.</td></tr>
  <tr><td><strong>STR</strong></td><td>Suspicious transaction report, filed with the FIC.</td></tr>
  <tr class="alt"><td><strong>FIC</strong></td><td>The Financial Intelligence Centre of Rwanda.</td></tr>
</table>

<h2>4. Governance and responsibilities</h2>
<p><strong>Managing Director.</strong> The Managing Director is accountable for this policy, approves it, and ensures that Cypadi has the resources to apply it.</p>
<p><strong>Compliance Officer.</strong> Cypadi appoints a Compliance Officer as its money laundering reporting officer. The Compliance Officer:</p>
<ul>
  <li>owns and maintains this policy and the Vendor Due Diligence Procedures;</li>
  <li>reviews vendor applications and approves or rejects them before any product is published;</li>
  <li>reviews monitoring alerts and decides whether a transaction is released, held, refunded or reported;</li>
  <li>files suspicious transaction reports with the FIC where required, and keeps the register of reports;</li>
  <li>maintains the record of staff briefings and this policy review cycle.</li>
</ul>
<p>Name, title and contact: <span class="ph">[[COMPLIANCE_OFFICER]]</span>, Compliance Officer, compliance@cypadi.com, <span class="ph">[[CO_PHONE]]</span>.</p>
<p><strong>All staff.</strong> Every member of staff, contractor and administrator must read this policy, must not process a transaction they know or suspect to be unlawful, and must report suspicion to the Compliance Officer the same day.</p>

<h2>5. Customer due diligence</h2>
<h3>5.1 Standard due diligence (all customers)</h3>
<p>The following is collected from every customer and constitutes the standard CDD record:</p>
<ul>
  <li>at registration: full name, email address, phone number and username;</li>
  <li>at checkout: delivery address, city, province, country, the items ordered, the order value, and the payment reference of the mobile money deposit.</li>
</ul>
<p>This record is sufficient to identify the payer and to reconcile every order to a single payment. No product is dispatched unless the payment has been confirmed by the payment partner.</p>

<h3>5.2 Enhanced due diligence (EDD)</h3>
<p>Cypadi obtains a copy of a government-issued identity document (national identity card or passport), and records the source of funds where relevant, before completing a transaction where any of the following applies:</p>
<ul>
  <li>a single order exceeds <strong>RWF 500,000</strong>;</li>
  <li>cumulative purchases exceed <strong>RWF 2,000,000</strong> within a rolling period of thirty (30) days;</li>
  <li>the name or phone number of the payer differs materially from the registered account holder;</li>
  <li>the customer is a politically exposed person, a family member or a known close associate;</li>
  <li>the transaction has been flagged under section 9.</li>
</ul>
<p>Where satisfactory identification cannot be obtained, Cypadi cancels the order and refunds the payment to the original paying number through the same provider.</p>

<h2>6. Vendor due diligence</h2>
<p>Before any vendor products appear on shop.cypadi.com, Cypadi verifies the vendor identity, business details and product categories, and a Compliance Officer approves the application. Until that approval is recorded, the vendor cannot publish any listing. The detailed checks, risk rating and approval record are set out in the <strong>Vendor Due Diligence Procedures (CYP-VDD-001)</strong>, which is incorporated into this policy by reference.</p>

<h2>7. Risk assessment</h2>
<p>Customers, vendors and transactions are rated <strong>low</strong>, <strong>medium</strong> or <strong>high</strong> risk. Ratings are recorded, drive the level of due diligence applied, and are reviewed at least annually and whenever new information arises.</p>
<table>
  <tr><th style="width:14%">Rating</th><th style="width:43%">Indicators</th><th>Applied controls</th></tr>
  <tr><td><strong>Low</strong></td><td>Verified identity; consistent order pattern; delivery within Rwanda; no adverse findings.</td><td>Standard CDD; automated monitoring under section 9.</td></tr>
  <tr class="alt"><td><strong>Medium</strong></td><td>Unverified business; recently established vendor; higher-fraud product categories such as electronics and mobile phones; orders near the EDD threshold.</td><td>Standard CDD plus verification of the business registration; manual review every six months.</td></tr>
  <tr><td><strong>High</strong></td><td>Politically exposed person; adverse media; identity inconsistent with the payer; repeated failures or refunds; anything Cypadi cannot verify.</td><td>EDD before the transaction; Compliance Officer approval for each order; review every three months.</td></tr>
</table>

<h2>8. Sanctions, PEP and adverse media screening</h2>
<p>At onboarding and at least annually thereafter, Cypadi screens vendor owners and any high-value customer against:</p>
<ul>
  <li>the United Nations Security Council consolidated sanctions list;</li>
  <li>the OFAC Specially Designated Nationals list of the United States Treasury;</li>
  <li>the European Union consolidated sanctions list;</li>
  <li>the Rwandan gazette of designated persons and entities;</li>
  <li>adverse media reporting relating to financial crime.</li>
</ul>
<p>The screening result, the lists consulted and the date of screening are recorded in the customer or vendor file. A confirmed match results in refusal of onboarding or termination of the relationship, freezing or reversal of the affected transaction, and a report to the FIC where required.</p>

<h2>9. Transaction monitoring</h2>
<p>Cypadi reviews transactions daily against the following indicators. Any alert is examined by the Compliance Officer within one business day of being raised.</p>
<ul>
  <li>three or more failed payment attempts followed by a successful one from the same number;</li>
  <li>one phone number paying for three or more accounts with no apparent connection;</li>
  <li>a single order at or within five per cent of the provider published maximum;</li>
  <li>several orders to one delivery address paid from different numbers;</li>
  <li>repeated order-and-cancel or order-and-refund patterns;</li>
  <li>an order placed from a device or network inconsistent with the account history;</li>
  <li>a vendor listing products unrelated to its declared business category.</li>
</ul>
<p>Cypadi may delay, hold, decline or reverse any transaction while an investigation is open, and may suspend the account concerned.</p>

<h2>10. Suspicious transaction reporting</h2>
<p>Any member of staff who becomes aware of activity they know or suspect to be linked to money laundering or terrorist financing must report it to the Compliance Officer immediately and in writing. The Compliance Officer assesses the report, records the decision and its reasons, and where Cypadi knows or suspects money laundering or terrorist financing, files a suspicious transaction report with the FIC without delay.</p>
<p>Cypadi does not disclose to the customer, or to any third party, that a report has been made or is contemplated. Reports and the register of reports are held by the Compliance Officer and are not stored with ordinary customer records.</p>

<h2>11. Record keeping and retention</h2>
<p>Cypadi retains identification data, order and deposit records, vendor files, screening results, monitoring alerts, investigation files and reports for <strong>five (5) years after the end of the customer or vendor relationship</strong>, or for such longer period as the FIC or a competent authority directs. Records are stored on access-controlled systems, identity documents are served only through an authenticated route and never as public files, and records are produced to the payment partner, the mobile money operators, the FIC or any competent authority on request.</p>

<h2>12. Third-party payment providers</h2>
<p>Customer payments are collected in Rwandan francs through <strong>PawaPay Ltd</strong> and settled through the licensed mobile money operators <strong>MTN Mobile Money Rwanda</strong> and <strong>Airtel Money Rwanda</strong>. Cypadi relies on these regulated partners for the execution of the payment. Cypadi remains responsible for the identity of its customers and vendors, for the legitimacy of the orders it accepts, and for its own settlement obligations.</p>

<h2>13. Refusal, suspension and termination</h2>
<p>Cypadi may decline to onboard a customer or vendor, refuse or cancel an order, suspend or close an account, withhold a settlement, restrict access to the platform, or terminate a relationship where this policy or the Vendor Due Diligence Procedures are not met, where identification cannot be completed, or where suspicion of financial crime arises. The decision and its reasons are recorded. Where a payment has already been collected and the order cannot proceed, the payment is refunded to the original paying number.</p>

<h2>14. Personal data protection</h2>
<p>Personal data collected under this policy is processed only for the purposes set out in the Cypadi Privacy Policy, in line with <strong>Law No. 058/2021 on the protection of personal data and privacy</strong>. A customer or vendor may request access to, correction of, or deletion of their personal data by writing to compliance@cypadi.com; requests are answered within thirty (30) days.</p>

<h2>15. Staff training and awareness</h2>
<p>Every member of staff, contractor and administrator with access to customer data, orders or payments is briefed on this policy at induction and at least annually thereafter. The briefing covers identification requirements, the monitoring indicators in section 9, the escalation route to the Compliance Officer and the prohibition on tipping off. Attendance is recorded and signed by the Compliance Officer.</p>

<h2>16. Review and amendment</h2>
<p>This policy is reviewed at least every twelve (12) months, and immediately on any material change in the Cypadi business model, product range, payment providers or applicable law, or following any serious compliance incident. Amendments are recorded in the version history table, approved by the Managing Director and issued as a new version number.</p>

<h2>17. Approval</h2>
<p>This policy was approved by the Managing Director of Cypadi Ltd on the date shown below and takes effect from that date.</p>
'@
