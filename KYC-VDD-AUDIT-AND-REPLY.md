# Cypadi Ltd — Audit of the two PawaPay documents, and the reply

**Prepared:** 10 November 2026
**Files audited:** `KYC and AML Policy (Final).docx`, `Vendor Due Diligence Procedures (Final).docx`

---

## 1. Direct answers to your three questions

### Does the KYC and AML Policy have the company logo? — NO

I opened the `.docx` at the archive level. The file contains **no image at all**:

| Part of the file | Present? |
|---|---|
| `word/media/` (any embedded picture) | **Missing — the folder does not exist** |
| `word/header1.xml` (page header) | **Missing** |
| `word/footer1.xml` (page footer, page numbers) | **Missing** |
| `docProps/thumbnail.jpeg` | Present, but this is only the Windows Explorer preview icon. It is never printed and is not a logo. |

"CYPADI" appears on the page only as the first paragraph, typed in capital letters in the same font as the body text.

### Does the Vendor Due Diligence Procedures document have the logo? — NO

Exactly the same: no `word/media/`, no header, no footer, no image of any kind.

### Does either document have the company stamp? — NO, and neither can have one

A stamp in a `.docx` is an image (a scan or a PNG). Both documents contain **zero images**, so there is no stamp and no place reserved for one. I also searched this machine for a stamp or seal image — there is no `*stamp*` or `*seal*` file anywhere, so your company stamp has never been digitised.

### Is there a signature block? — NO

There is no "Signed by", no name, no title, no date line, no approval line, and no "Approved by". Both documents simply end with the sentence *"This policy may be updated as Cypadi's business operations and compliance requirements evolve."* Nothing signed it.

---

## 2. Verdict: are these documents "strong"?

**No. As they stand they will not be accepted as approved company documents.** They read as drafts, not as issued policies.

There are two separate problems: the **form** (no logo, no stamp, no signature, no document control) and the **substance** (missing the sections a compliance reviewer expects).

### Form — what is missing

| # | Missing element | Why it matters |
|---|---|---|
| 1 | Company logo in the page header | The first thing a reviewer checks for authenticity. You have the logo at `https://cypadi.com/logo/cypadi_trancyparent_logo.png`. |
| 2 | Company stamp | A Rwandan corporate convention. Its absence reads as "not yet a real company". |
| 3 | Signature of the Managing Director, with name, title and date | A policy with no accountable signatory is treated as unapproved. |
| 4 | Legal entity name — **Cypadi Ltd** | The documents say only "CYPADI". A reviewer must be able to see who they are contracting with. |
| 5 | TIN and RDB registration number | Standard on any corporate document. |
| 6 | Registered address | Same. |
| 7 | Document owner and approver | "Who owns this policy?" — there is no answer in the text. |
| 8 | Document reference and version history table | You have "Version 1.0" in the body, but no document ID and no change history. |
| 9 | Next review date | You have "Effective Date: July 2026", which is in the past, and no review cycle. |
| 10 | Page numbers and a footer | Multi-page documents without page numbers look unfinished and cannot be referenced. |

### Substance — what is missing

**KYC and AML Policy**

| # | Gap | Detail |
|---|---|---|
| 1 | No Rwandan law cited | It says "applicable anti-money laundering requirements". Name them: **Law No. 058/2023 on the prevention and punishment of money laundering offences**, and the **Financial Intelligence Centre (FIC)**. |
| 2 | No named Compliance Officer / MLRO | Reviewers want a human being, with an email and a phone number. |
| 3 | No CDD / EDD distinction | There is no threshold that triggers identity verification. This is the core of a KYC policy. |
| 4 | No PEP, sanctions or adverse media screening | The single most common reason a KYC policy is sent back. |
| 5 | No risk rating | No low / medium / high classification and no consequence per level. |
| 6 | No suspicious transaction reporting process | No mention of the FIC or of an STR. |
| 7 | No retention period | It says "stored securely for operational and business purposes" — no number. State **five years**. |
| 8 | No mention of PawaPay or the mobile money operators | Your policy should acknowledge the payment chain it depends on. |
| 9 | Hedging language throughout | "may be collected", "may request", "may process". In a compliance document, "may" is read as "we do not actually do this". Use "is collected", "we request", "we process". |

**Vendor Due Diligence Procedures**

| # | Gap | Detail |
|---|---|---|
| 1 | "Before a vendor is approved as a seller", with no described approval step | Good news: your system now **does** enforce this (`seller_status` = pending / approved / rejected, with an admin approval screen). The document should describe how the gate works, not just assert it. |
| 2 | No decision record | Who approved, when, at what risk rating, for which product categories. |
| 3 | No prohibited-products list | "Illegal goods are not permitted" is circular. List the categories you refuse. |
| 4 | No vendor risk rating | Low / medium / high, with re-review intervals. |
| 5 | No sanctions / PEP screening | Same as above. |
| 6 | No retention period | **Five years.** |
| 7 | No settlement terms | It says payouts "may be processed… where applicable". That is the weakest sentence in the pack and invites the follow-up: *which is it?* State the settlement rule precisely. |
| 8 | No refusal / suspension / termination consequences | "Accounts may be suspended" is too vague. |
| 9 | No staff training | Two lines are enough, but they must be there. |

---

## 3. Is the stamp actually necessary?

**Not strictly.** PawaPay's onboarding team is a fintech compliance team; what they primarily require is company registration, TIN, directors' IDs, a board resolution and signed policies. A rubber stamp is **not** on their standard checklist.

**But add it anyway**, for three reasons:

1. A Rwandan reviewer expects a policy to be signed and stamped. Its absence is noticed even when it is not formally required.
2. It costs ten minutes and removes an objection permanently.
3. The same documents may later go to a bank, to RRA, or to the FIC, where the stamp is expected.

**Do not** let anyone generate a stamp image for you. A company seal must be the real seal. The correct procedure is:

1. Add the signature block at the end of each document.
2. Print both documents.
3. The Managing Director signs on the line, by hand.
4. Apply the company stamp inside the reserved box.
5. Scan both back to PDF at 300 dpi.
6. Send **the scanned PDF**, not the `.docx`.

That scanned, signed, stamped PDF is the strongest form the document can take. Keep the unsigned `.docx` as the working master.

---

## 4. Exact checklist to fix both documents

Both documents:

- [ ] Cypadi logo in the page header, on every page. Use `https://cypadi.com/logo/cypadi_trancyparent_logo.png` at about 74 pt wide.
- [ ] A document-control table at the top: legal entity **CYPADI LTD**, RDB number, **TIN**, registered address, document reference, version, effective date, owner, approver, next review date, classification.
- [ ] A version-history table (1.0 → 1.1).
- [ ] A footer with "Cypadi Ltd | document title | Internal – Confidential" and **Page X of Y**.
- [ ] A signature block: name, title, signature line, date, and a **dashed box marked "COMPANY STAMP"**.
- [ ] The placeholders below, filled in before printing.

Placeholders you must supply:

| Placeholder | Value |
|---|---|
| `[[TIN]]` | your RRA Tax Identification Number |
| `[[RDB_NUMBER]]` | your RDB registration number |
| `[[REGISTERED_ADDRESS]]` | your registered address in Kigali |
| `[[MD_NAME]]` | full name of the Managing Director |
| `[[COMPLIANCE_OFFICER]]`, `[[CO_PHONE]]` | the named Compliance Officer and phone number |
| `[[EFFECTIVE_DATE]]`, `[[SIGN_DATE]]` | the date you sign |

---

## 5. Ready-to-send reply

> **Subject:** Cypadi Ltd — KYC/AML Policy and Vendor Due Diligence Procedures (CYPADI LTD account)
>
> Dear [Name],
>
> Thank you for your email. Please find attached our due-diligence documents for the CYPADI LTD account.
>
> **Documents attached**
>
> 1. KYC and Anti-Money Laundering Policy — CYP-AML-001, version 1.1 (PDF, signed and stamped)
> 2. Vendor Due Diligence Procedures — CYP-VDD-001, version 1.1 (PDF, signed and stamped)
> 3. Company TIN Certificate (RRA)
> 4. Directors' and shareholders' identification documents
> 5. Board resolution authorising the signatory of the PawaPay account
>
> The company registration certificate and the corporate records submitted with the initial application for the CYPADI LTD account are already on file with you and are not re-attached here. Please let me know if you would like a fresh copy.
>
> **Company details**
>
> Cypadi Ltd is a private limited company registered in Rwanda (RDB: [number]; TIN: [number]), operating **shop.cypadi.com**, an online marketplace where independent vendors list products and customers pay in RWF by MTN Mobile Money or Airtel Money. Cypadi reviews every vendor before any product is published: we collect the owner's national identity document, business registration and product categories, and a Compliance Officer approves the application before the vendor can list anything. The platform enforces this gate technically — a vendor whose status is not *approved* cannot publish a listing.
>
> **What the two policies cover**
>
> - Customer due diligence at registration and checkout, with enhanced due diligence (identity document, source of funds) triggered above RWF 500,000 per order or RWF 2,000,000 in a rolling 30-day period.
> - Vendor due diligence, risk rating, sanctions/PEP screening and a prohibited-products list.
> - Daily transaction monitoring, escalation to a named Compliance Officer, and suspicious transaction reporting to the Financial Intelligence Centre.
> - Five-year record retention, on access-controlled systems.
>
> If anything in the pack is incomplete, please tell me exactly what is missing and I will send it the same day. I am also happy to walk your team through the checkout flow on a short call.
>
> Kind regards,
>
> **[Your full name]**
> [Your title], Cypadi Ltd
> [mobile] · info@cypadi.com · shop.cypadi.com

---

## 6. Two decisions you must make before you send

1. **Vendor settlements.** Your documents say Cypadi "may process payouts to vendors". That describes a disbursement activity — a heavier regulatory posture that invites the questions *who is licensed*, *whose money is it*, and *what happens if you cannot pay*. If you genuinely settle vendors from your own account after an order completes, keep the section but state the settlement rule precisely. If you do not, delete it. **Decide before you send, because "we changed our model" after going live is far more expensive than a precise sentence today.**

2. **The documents must match the website.** A reviewer will click through shop.cypadi.com before opening the PDF. The site, the policies and the payment integration must tell the same story on the day you press send.

---

## 7. Internal — not part of the email

The items below are configuration and consistency facts, not email content, so they are not in the letter. They still have to be settled on your side before the account can process a real payment.

1. **Callback endpoint.** PawaPay has `https://payment.cypadi.com/api/v1/webhook/unified.php` registered, while this application receives at `https://shop.cypadi.com/pawapay-callback.php`. If the registered host does not route to `pawapay-callback.php`, a completed payment is never marked as paid in `my-orders.php`. Fix the URL in the PawaPay dashboard; if the field is locked, ask support to change it.

2. **Signed callbacks.** The account shows `signedCallbacks: false`, and `pawapay-callback.php` currently accepts an unsigned POST, so anyone who knows a `depositId` can mark an order paid. Ask support to enable signature verification, then validate the signature header before updating the order.

3. **Business model wording.** The reply describes shop.cypadi.com as "an online marketplace where independent vendors list products", but the public pages were rewritten to the merchant-of-record model ("Cypadi Shop is a Rwandan online store operated by Cypadi Ltd"), with no "independent seller" or "payout" wording anywhere. A reviewer reading the email and then the site will see two different businesses. Align one of the two before sending.
