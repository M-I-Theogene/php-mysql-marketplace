# Cypadi Ltd — Audit of the two PawaPay documents, and the reply

**Prepared:** 10 November 2026
**Subject files:** `KYC and AML Policy (Final).docx`, `Vendor Due Diligence Procedures (Final).docx`

---

## 0. Status — where the pack stands now

Your original two files were audited, found to be drafts, and have been rewritten and rebuilt. What is on disk now is a finished, signed, stamped and placeholder-free pair of documents.

| File | Pages | Purpose |
|---|---|---|
| `pawapay-documents/Cypadi Ltd - KYC and AML Policy - CYP-AML-001 v1.1.docx` | 7 | Editable master |
| `pawapay-documents/Cypadi Ltd - KYC and AML Policy - CYP-AML-001 v1.1.pdf` | 7 | **Send this** |
| `pawapay-documents/Cypadi Ltd - Vendor Due Diligence Procedures - CYP-VDD-001 v1.1.docx` | 8 | Editable master |
| `pawapay-documents/Cypadi Ltd - Vendor Due Diligence Procedures - CYP-VDD-001 v1.1.pdf` | 8 | **Send this** |

Everything below was checked inside the files themselves — the `.docx` package, the embedded images, and all 16 rendered PDF pages — not assumed.

| Element | KYC policy | VDD procedures |
|---|---|---|
| Company logo, page header, every page | present (`image1.png`, 76 pt) | present (`image1.png`, 76 pt) |
| Gold rule under the header | present | present |
| Footer: entity, title, "Internal – Confidential", live `Page X of Y` | present | present |
| Document-control table | present | present |
| Version-history table (1.0 → 1.1) | present | present |
| **MD's signature image**, embedded | present (`image2.png`) | present (`image2.png`) |
| **Company stamp image**, embedded | present (`image3.png`) | present (`image3.png`) |
| Name, title, date under the signature | Pacifique MASENGESHO · Managing Director · 10 November 2026 | same |
| RDB registration number | 123088034 | 123088034 |
| Registered address | Kimironko, Gasabo, Kigali, Rwanda | same |
| `[[PLACEHOLDER]]` tokens remaining | **none** | **none** |

Because the signature and the stamp are now real images taken from `pawapay-documents/sign.png` and `pawapay-documents/stamp.png`, you no longer have to print, sign, stamp and rescan anything. The PDFs are ready to attach as they are. Keep the `.docx` masters for future edits.

**One field is deliberately absent: the TIN.** The RDB certificate (`CYPADI Ltd.pdf`) carries the company code, the registration date, the registered office, the directors and the shareholders, but no Tax Identification Number. Rather than invent one, both documents print the RDB number and the registered address only. If you want the TIN in the document-control table, type it into the master `.docx` in the cell under "Registered address", then re-export — you have the RRA certificate in hand and I do not have the number.

---

## 1. Your original question, answered about the files as you sent them

You asked, of `KYC and AML Policy (Final).docx` and `Vendor Due Diligence Procedures (Final).docx`: are they strong, do they carry the logo, do they carry the stamp? Here is what I found in those two files, before anything was rebuilt.

### Was the company logo there? — No

I opened the `.docx` at the archive level. Neither file contained a single image:

| Part of the file | Present in the originals? |
|---|---|
| `word/media/` (any embedded picture) | **Missing — the folder did not exist** |
| `word/header1.xml` (page header) | **Missing** |
| `word/footer1.xml` (page numbers) | **Missing** |
| `docProps/thumbnail.jpeg` | Present, but this is only the Windows Explorer preview icon. It never prints and it is not a logo. |

"CYPADI" appeared on the page as the first paragraph of body text, typed in capitals in the same font as everything else.

### Was the stamp there? — No, and it could not have been

A stamp inside a `.docx` is an image. Both files contained zero images, so there was neither a stamp nor anywhere reserved to place one. At the time, no `*stamp*` or `*seal*` file existed on this machine either — the company seal had never been digitised. You have since supplied `pawapay-documents/stamp.png` and `pawapay-documents/sign.png`, and both are now embedded.

### Was there a signature block? — No

No "Signed by", no name, no title, no date line, no approval line. Both documents simply ended on the sentence *"This policy may be updated as Cypadi's business operations and compliance requirements evolve."* Nothing signed them.

---

## 2. Verdict on the originals: were they strong?

**No.** As submitted they would not have been accepted as approved company documents. They read as drafts, and there were two separate classes of problem.

### Form

| # | Missing | Why a reviewer stops on it |
|---|---|---|
| 1 | Logo in the page header | The first authenticity check anyone makes. |
| 2 | Company stamp | A Rwandan corporate convention; its absence reads as "not yet a real company". |
| 3 | Signed, named, dated approval | A policy with no accountable signatory is treated as unapproved. |
| 4 | Legal entity name — **Cypadi Ltd** | The files said only "CYPADI". A reviewer must see who they are contracting with. |
| 5 | RDB number and registered address | Standard on any corporate document. |
| 6 | Document owner and approver | "Who owns this policy?" had no answer in the text. |
| 7 | Document reference and version history | "Version 1.0" appeared in the body, with no document ID and no change log. |
| 8 | Next review date | The effective date was July 2026 — already in the past — with no review cycle. |
| 9 | Page numbers and footer | A multi-page document without page numbers cannot be referenced in correspondence. |

### Substance — KYC and AML Policy

| # | Gap | Detail |
|---|---|---|
| 1 | No Rwandan law cited | "Applicable anti-money laundering requirements" is not a citation. Now names **Law No. 058/2023** and the **Financial Intelligence Centre (FIC)**. |
| 2 | No named Compliance Officer | Reviewers want a person, with an email and a phone number. |
| 3 | No CDD / EDD distinction | No threshold triggered identity verification — the core of a KYC policy. |
| 4 | No PEP, sanctions or adverse-media screening | The commonest reason a KYC policy is returned. |
| 5 | No risk rating | No low/medium/high and no consequence per level. |
| 6 | No STR process | No mention of the FIC or of filing a suspicious transaction report. |
| 7 | No retention period | "Stored securely" with no number. |
| 8 | No mention of PawaPay or the mobile money operators | The payment chain the business depends on was invisible. |
| 9 | Hedging throughout | "may be collected", "may request", "may process". In a compliance document "may" reads as "we do not actually do this". |

### Substance — Vendor Due Diligence Procedures

| # | Gap | Detail |
|---|---|---|
| 1 | "Before a vendor is approved as a seller", with no described approval step | The platform really does enforce this — the document had to describe how, not just assert it. |
| 2 | No decision record | Who approved, when, at what risk rating, for which categories. |
| 3 | No prohibited-products list | "Illegal goods are not permitted" is circular. |
| 4 | No vendor risk rating or re-review interval | — |
| 5 | No sanctions or PEP screening | — |
| 6 | No retention period | — |
| 7 | No settlement terms | Payouts "may be processed… where applicable" was the weakest sentence in the pack: *which is it?* |
| 8 | No refusal / suspension / termination consequences | "Accounts may be suspended" is too vague to rely on. |
| 9 | No staff training | Two lines suffice, but they must exist. |

---

## 3. What was rebuilt

Both documents were rewritten against the platform as it actually behaves, then typeset as proper corporate documents.

- **Structure:** document control, version history, numbered sections, cross-references that resolve (`CYP-AML-001` ↔ `CYP-VDD-001`), glossary, approval page.
- **KYC and AML Policy, 7 sections' worth of substance:** Rwandan law and the FIC named; a named Compliance Officer with direct contact; customer due diligence, with a risk-based enhanced due diligence regime requiring an identity document and source of funds on a mismatched payer, a politically exposed person, a high-risk rating, an order pattern inconsistent with the account history, or a transaction flagged by monitoring; sanctions, PEP and adverse-media screening; low/medium/high risk rating with re-review intervals; daily monitoring indicators; STR reporting; five-year retention; the PawaPay / MTN / Airtel payment chain described; settlement rule; staff training; review cycle.
- **VDD Procedures, 12 sections:** the approval gate described technically — `seller_status` pending / approved / rejected, enforced on every seller page, with the admin review screen, the streamed identity document and who may open it; verification standards; prohibited-product list; vendor risk rating; screening; ongoing monitoring; decision record; settlement rule; refusal, suspension and termination; five-year retention; training; review cycle.
- **Typography:** A4, 2.12 cm side margins, Calibri 10.5 pt justified body, bronze small-caps subtitle, gold rules under the logo header and every section heading, dark navy table headers with alternating row shading, table header rows that repeat across page breaks, rows that never split mid-cell, footer with live `Page X of Y` fields.

### Note on the PDF export, if you ever rebuild

The Word → PDF step is picky. Two things matter and both are now fixed in `_tmp_convert_clean.ps1`:

1. Exporting with `$word.ScreenUpdating = $false` silently drops every paragraph border, so the gold rules vanish from the PDF while remaining in the `.docx`. The clean converter leaves `ScreenUpdating` alone.
2. Word writes paragraph borders into the PDF only if `w:pBdr` sits in the correct schema position inside `w:pPr` (before `w:spacing` and `w:jc`). The builder already inserts it correctly.

With both in place, a rebuild produces 27 gold rules per document, on every page.

---

## 4. Your corrections — all applied

1. **"Two points I would be grateful for your guidance on"** — that passage has been removed from the reply. It is not in the email below.
2. **RDB Certificate of Company Registration — CYPADI LTD** — removed from the attachment list, because the certificate was uploaded at the initial onboarding stage and is already on file with PawaPay.
3. **The TIN certificate, the directors' and shareholders' identity documents, and the board resolution** — removed from the attachment list, and then removed from the letter altogether. PawaPay already holds and has already reviewed all three, so they are not mentioned at all: no listing, no reminder that they are on file, no offer to re-send.
4. **The letter was cut down.** The "Company details" paragraph — which explained how vendors are reviewed and how the platform enforces the approval gate — is gone. It was arguing a case nobody had put to you, and it named a technical control (`seller_status`) that a reviewer can test. The walkthrough offer and the closing "tell me if anything is missing" line are gone too. What is left is: two attachments, four short bullets on what the policies cover, and the signature.

The letter contains no reference to the corporate file in any form — it is simply not part of this submission. The callback-URL and signed-callback findings sit in §7, so nothing that is not meant for PawaPay's eyes is in the letter.

---
## 5. Ready-to-send reply

> **Subject:** Cypadi Ltd — KYC/AML Policy and Vendor Due Diligence Procedures (CYPADI LTD account)
>
> Dear [Name],
>
> Thank you for your email. Please find attached the two documents you requested for the CYPADI LTD account:
>
> 1. KYC and Anti-Money Laundering Policy — CYP-AML-001, version 1.1
> 2. Vendor Due Diligence Procedures — CYP-VDD-001, version 1.1
>
> **What the two policies cover**
>
> - Customer due diligence at registration and checkout, and enhanced due diligence on defined risk indicators. Order value alone never triggers EDD and never blocks a sale.
> - Vendor due diligence, risk rating, sanctions/PEP screening and a prohibited-products list.
> - Daily transaction monitoring, escalation to a named Compliance Officer, and reporting to the Financial Intelligence Centre.
> - Five-year record retention on access-controlled systems.
>
> Kind regards,
>
> **Pacifique MASENGESHO**
> Managing Director, Cypadi Ltd
> +250 792 359 800 · info@cypadi.com · shop.cypadi.com

---

## 6. Two decisions you must make before you send

1. **Vendor settlements.** The documents state a settlement rule: Cypadi collects customer payments through PawaPay into its own settlement account and settles approved vendors afterwards. That describes a disbursement activity — a heavier regulatory posture, which invites the questions *who is licensed*, *whose money is it*, and *what happens if you cannot pay*. Keep the section if that is genuinely your model. If it is not, change it before you send: "we changed our model" after going live is far more expensive than a precise sentence today.

2. **The documents must match the website.** A reviewer will click through shop.cypadi.com before opening the PDF. The site, the policies and the payment integration must tell the same story on the day you press send.

---

## 7. Internal — not part of the email

These are configuration and consistency facts, not email content, so they are not in the letter. They still have to be settled before the account can process a real payment.

1. **Callback endpoint.** PawaPay has `https://payment.cypadi.com/api/v1/webhook/unified.php` registered, while this application receives at `https://shop.cypadi.com/pawapay-callback.php`. If the registered host does not route to `pawapay-callback.php`, a completed payment is never marked as paid in `my-orders.php`. Fix the URL in the PawaPay dashboard; if the field is locked, ask support to change it.

2. **Signed callbacks.** The account shows `signedCallbacks: false`, and `pawapay-callback.php` currently accepts an unsigned POST, so anyone who knows a `depositId` can mark an order paid. Ask support to enable signature verification, then validate the signature header before updating the order.

3. **Business model wording.** The reply describes shop.cypadi.com with independent vendors and settlement, but the public pages were rewritten to the merchant-of-record model ("Cypadi Shop is a Rwandan online store operated by Cypadi Ltd"), with no "independent seller" or "payout" wording anywhere. A reviewer reading the email and then the site will see two different businesses. Align one of the two before sending.
