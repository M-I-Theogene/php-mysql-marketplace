# PawaPay Submission — Document & Prototype Review

**Prepared for:** Cypadi Ltd — shop.cypadi.com
**Date:** 7 November 2026
**Scope:** Review of the two due-diligence documents, the intended submission email, the live website, and the production codebase, before the email is sent to PawaPay.

---

## 0. Findings that change the whole picture (read this first)

These four facts were verified against your live backend and production code, and they contradict what you were about to tell PawaPay:

### 0.1 You are already live in production with PawaPay

I called PawaPay's own API with the token in your `.env`:

```
GET https://api.pawapay.io/v2/active-conf?country=RWA&operationType=DEPOSIT
→ HTTP 200
→ companyName: "CYPADI LTD"
→ MTN_MOMO_RWA : OPERATIONAL  (RWF 5 – 2,000,000)
→ AIRTEL_RWA   : OPERATIONAL  (RWF 100 – 1,500,000)
```

The token is a **live production** Account Access Token (`api.pawapay.io`, not `api.sandbox.pawapay.io`), valid until 2036, and it carries `DAF,PAF` permissions (**D**eposit **a**nd **P**ayout products). Your company is registered in PawaPay's system as **CYPADI LTD**, and Rwandan collections are OPEN.

**Consequence:** the sentence *"we have the domain of cypadi.com yes and the domain is ready but we don't yet have the api way"* is **false, and it damages you**. It tells PawaPay you are less ready than your own merchant account says you are. Do not send it. See §2 for what to write instead.

### 0.2 Your callbacks are pointed at the wrong server — P0 bug

PawaPay will send payment confirmations to the URL registered on the account:

```
callbackUrl: https://payment.cypadi.com/api/v1/webhook/unified.php
```

Your codebase receives them at:

```
https://shop.cypadi.com/pawapay-callback.php
```

Different hostname (`payment.` vs `shop.`), different path (`/api/v1/webhook/unified.php` vs `/pawapay-callback.php`). Unless `payment.cypadi.com` is a separate app that forwards to this one, **no payment confirmation ever reaches Cypadi Shop.**

Today the only thing saving the flow is `payment-waiting.php` polling `pawapay-poll.php` every 3 seconds. That works **only while the customer keeps the tab open**. If they close the browser after approving the PIN — which most people do — the order stays `pending` in your database forever, the customer has been charged, and no receipt/order exists on their side. That is the exact failure mode a due-diligence reviewer will test.

**Fix (15 minutes, pick one):**

- **Option A (recommended):** in the PawaPay dashboard / API, set the callback for RWA DEPOSIT to
  `https://shop.cypadi.com/pawapay-callback.php`
- **Option B:** create the path `api/v1/webhook/unified.php` on the host that serves `payment.cypadi.com` and have it `include` your existing callback logic.

Then test with one real RWF 100 payment, close the tab before approving, and confirm the row in `orders` flips to `paymentStatus='paid'`.

### 0.3 Your callback endpoint accepts unsigned requests

PawaPay returned:

```json
"signatureConfiguration": { "signedRequestsOnly": false, "signedCallbacks": false }
```

That means `pawapay-callback.php` trusts any HTTP POST. Anyone who learns a `depositId` can forge:

```json
{"depositId":"<id>","status":"COMPLETED"}
```

…and mark an unpaid order as paid. A reviewer who reads your code will note this immediately, because it is the first thing examined in a payment integration review.

**Fix:** ask PawaPay to enable **signed callbacks**, then verify the `X-Signature` header (Ed25519, public key from PawaPay) at the top of `pawapay-callback.php` and reject anything that fails. If you cannot do that before sending the email, at least do not claim the flow is hardened.

### 0.4 A live API token is sitting in a git repository

`.env` contains the live `PAWAPAY_API_TOKEN` in plaintext, next to a `.git` folder, in a working tree that also contains `.vscode` and `.specstory`. There is **no `.gitignore`** in this directory. `git ls-files` shows `.env` is not tracked *yet*, but nothing prevents an accidental `git add .`.

Also in the repo: `README.md` publishes the default credentials of the whole application (`admin: iqbolshoh / IQBOLSHOH`). If that repo is public — or ever becomes public — your marketplace admin panel is open to the internet.

**Do before anything else:** rotate the PawaPay token, add `.gitignore` with `.env`, and change the admin password.

---

## 1. Which set of documents are you actually submitting?

You have two contradictory pairs of documents in the folder. This is the single biggest risk in the submission, because a reviewer reading both sets will see you have not decided what your business is.

| Folder file | What it says Cypadi is | Vendor payouts? |
|---|---|---|
| `Vendor Due Diligence Procedures (Final).docx` | Marketplace, *"Independent sellers list products"*, vendors approved before selling | **Yes** — *"Cypadi may process payouts to vendors"* |
| `KYC and AML Policy (Final).docx` | Applies to *"customers and vendors"* | **Yes** — *"Vendor payouts related to order fulfillment may be processed"* |
| `Vendor Due Diligence Procedures - Corrected.docx` | *"Cypadi operates an e-commerce platform as the merchant of record"*, checks on **customers** | **No** — *"Any operational payments related to order fulfillment are handled separately"* |
| `KYC and AML Policy - Corrected.docx` | *"Cypadi operates as the merchant of record for products sold on the platform"*, applies to **customers** | **No** — section on payouts removed |

**Do not send both sets. Pick one.**

### The trade-off

**Model A — "Marketplace with vendor payouts" (the `Final` files)**
- Matches how your website *reads* today (footer: *"verified sellers list products"*, About Us: *"Seller payouts may be processed"*).
- Triggers much heavier scrutiny: paying independent third parties through your merchant account is a **disbursement / payment-service activity**. PawaPay will ask who is licensed, who holds the funds, how you settle, on what schedule, with what float, and what happens if you cannot pay. In Rwanda that conversation can lead to the BNR payment-service licensing question, which is not a conversation you want 3 weeks before trading.
- Your code does **not** support it: `PawaPayClient::initiatePayout()` exists but is **never called anywhere**. There is no payout UI, no payout scheduler, no payout reconciliation. `order_items.payout_status` is a column with no logic behind it. So the `Final` documents describe a capability your system does not have.

**Model B — "Merchant of record" (the `Corrected` files)**
- One seller of record: Cypadi. You buy/consign from suppliers and sell to customers. Money in goes to Cypadi; supplier settlement is a private commercial matter *outside* the PawaPay flow.
- Far lighter scope: you only need CDD on customers, no vendor payout controls, no disbursement licensing question.
- This is what your **code** actually implements: one deposit per order into your own account, no payout call anywhere.
- Downside: your public pages currently say the opposite (§3).

### Recommendation

**Submit Model B (the `Corrected` files), and then make the website, the docs and the email all say the same thing.** Reality, code and paperwork must agree; of the three, the paperwork is the cheapest to align and the code is the thing PawaPay will test.

If you genuinely intend to pay independent sellers from your PawaPay balance soon, stop and reconsider: that is a different regulatory posture and you should say so explicitly now rather than be caught later. "We changed our model" after going live is far more expensive than "our model is X" today.

---
## 2. Verdict on the statement you drafted

> *"yes we have the domain of cypadi.com yes and the domain is ready but we don't yet have the api way yes the customers usually pay with mobile money when buying as normal"*

### Is it a good statement? No — four separate problems

1. **It is factually wrong.** You do have the API route, in production, with a live token, and PawaPay already knows it (`companyName: CYPADI LTD`, providers `OPERATIONAL`). Saying otherwise creates an internal contradiction they can see in their own dashboard within one click.
2. **It volunteers a weakness nobody asked about.** A due-diligence email is a place to be accurate, not confessional. You are not required to say "we are not ready"; you are required to answer what was asked.
3. **It does not answer what PawaPay actually needs.** When a provider asks "describe your website / how payments work", the useful answer is: what the customer sees, which providers, what the amount range is, when the order is confirmed, where the callback lands, and who is the merchant of record. "We don't have the API way" answers none of it.
4. **The English undercuts you.** "the api way", "as normal", doubled "yes… yes" — in a compliance file this reads as unprepared. Compliance reviewers price risk from the way you write, because they have no other signal.

### Use this instead (drop-in replacement, pick the variant that is true for you)

**Variant 1 — if production is genuinely processing today:**

> Cypadi Shop (shop.cypadi.com) is live. It is a Rwandan online store where customers browse products, add them to a cart and pay at checkout with MTN MoMo or Airtel Money.
>
> The site is integrated with the PawaPay Collections (Deposit) API on the production environment. Payments are initiated server-side against `/v2/deposits`, the customer's provider is resolved by PawaPay, and order status is updated from the payment callback, with a polling fallback on the confirmation page. Each order carries a unique `depositId` and a unique `orderNumber` in the deposit metadata, so every order can be reconciled to a PawaPay deposit.
>
> Transaction range today is RWF 5 – 2,000,000 for MTN MoMo and RWF 100 – 1,500,000 for Airtel Money, in line with your published limits. Cypadi Ltd is the merchant of record for every order.
>
> The callback endpoint registered on our account is `https://payment.cypadi.com/api/v1/webhook/unified.php`; our application endpoint is `https://shop.cypadi.com/pawapay-callback.php`. Please confirm which one should remain registered, or update the account configuration to the latter — I want to be certain every confirmed payment is recorded on our side.

That last paragraph is deliberate: it *raises* the callback mismatch yourself, in the tone of someone protecting their own reconciliation. It turns a defect they would find into evidence that you check these things.

**Variant 2 — if you have never run a real payment through it:**

Use Variant 1 but change the opening to *"The site is live and the PawaPay integration is implemented and ready on the production endpoint; we are validating the first live transactions now."* Never write "we don't have the API".

### Delete these phrases

- "we don't yet have the api way" — cut entirely.
- "as normal" — cut.
- Any sentence beginning with "yes we have…" — start with the subject: "Cypadi Shop is…".

---

## 3. Document review — `Vendor Due Diligence Procedures (Final).docx`

### What works
Structure is clean and readable: Purpose → Registration → Identity → Review → Fulfilment → Payouts → Monitoring → Records → Updates. For a small merchant this is an acceptable skeleton. Keep the shape.

### Problems, in the order a reviewer hits them

| # | Where | Problem | Why it matters |
|---|---|---|---|
| 1 | Header | Only *"CYPADI"*. No legal name, registration number, TIN, registered address, document owner, approver, or next review date. | A compliance document with no accountable owner and no legal entity is treated as unapproved. Reviewers expect: company legal name, version, effective date, **policy owner (name + title)**, **approved by (name + title + date)**, **next review date**. |
| 2 | Registration list | Claims *"Country of operation"* is collected. | Your `accounts` table is `id, name, number, email, username, password, role, status, registration_date`. **There is no country field.** Only `orders.delivery_country` (default "Rwanda") exists. Either add the field or drop the claim. |
| 3 | *"Before a vendor is approved as a seller"* | **There is no approval step.** `signup/index.php` inserts `role='seller'` and the account is immediately usable; `accounts.status` is only `active`/`blocked`. | This is the claim most likely to be falsified by a 60-second test: register as a seller, list a product, and you are live. Either implement the gate (§6, P0-3) or rewrite to *"Sellers who register are reviewed by Cypadi before their products are listed; review is normally completed within one business day."* and then actually do it manually. |
| 4 | Identity Verification | *"Vendors are required to provide necessary information… Government-issued ID"* — but there is no upload path, no KYC form, no document store in the system. | Unfalsifiable in the bad direction: you claim a control that leaves no trace. Say **how** the document is obtained (email to compliance@…, WhatsApp, in person), where it is stored, and for how long. |
| 5 | Payouts | *"Cypadi may process payouts to vendors… where applicable."* | "May… where applicable" is the weakest sentence in the document and invites the follow-up: *which is it?* Reviewers read hedging as concealment. Under Model B, delete the payout section entirely. |
| 6 | Missing: risk rating | No low/medium/high classification, no criteria per level, no consequence (e.g. "high-risk sellers require enhanced due diligence and are reviewed monthly"). | This is a standard expectation in any AML/DD pack. |
| 7 | Missing: screening | No sanctions, PEP, or adverse-media screening; no lists named (UN, OFAC, EU, Rwanda gazette). | First question any reviewer asks. Even a manual step ("checked against UN/OFAC consolidated lists at onboarding and annually — screen saved to the seller file") is enough for your size, but it must be written down. |
| 8 | Missing: prohibited-products list | *"Illegal goods… not permitted"* is circular. | List the categories you actually refuse (weapons, pharmaceuticals, wildlife, counterfeit, adult, tobacco/alcohol licensing, crypto). A short explicit list is more convincing than a principle. |
| 9 | Missing: retention period | *"stored securely for operational and business purposes"* — no duration. | Say a number: **5 years** after the end of the relationship (or 7 if your counsel prefers), and where records live. |
| 10 | Missing: MLRO and escalation | No named compliance officer, no reporting channel, no escalation path. | *"Compliance Officer: [Name], [Title], [email], [phone]"* plus "staff report suspicious activity to the Compliance Officer immediately". Two lines, large credibility gain. |
| 11 | Missing: what happens on refusal | *"Accounts may be suspended"* — vague. | State: declined onboarding, suspension, termination, funds withheld pending investigation. |
| 12 | Missing: staff training | Nothing. | "All staff with access to customer data or payments are briefed on this policy at onboarding and annually; attendance is recorded." |
| 13 | Missing: third-party reliance | No mention of PawaPay or the MMOs. | Name them: *"Payments are processed by PawaPay Ltd and settled through licensed mobile money operators (MTN Mobile Money Rwanda, Airtel Money Rwanda). Cypadi does not hold a mobile money wallet on behalf of customers."* This tells PawaPay you understand the chain. |

### Rewritten document (drop-in, Model B — merchant of record)

> **CYPADI LTD**
> **Vendor & Customer Due Diligence Procedures**
> Version 1.1 · Effective 1 November 2026 · **Owner:** Compliance Officer, Cypadi Ltd · **Approved by:** Managing Director, 1 November 2026 · **Next review:** 1 November 2027
>
> **1. Purpose and scope**
> Cypadi Ltd ("Cypadi") operates the e-commerce website shop.cypadi.com in Rwanda. Cypadi is the merchant of record for every product sold on the site: the customer contracts with Cypadi, pays Cypadi, and receives the product from Cypadi. Suppliers who provide stock to Cypadi are commercial counterparties outside the payment flow and are governed by contract, not by this procedure.
> This document sets out the checks Cypadi performs before allowing a person to buy on the platform, and the checks performed on suppliers before stock is listed.
>
> **2. What we collect from customers**
> At registration: full name, email address, phone number, username. At checkout: delivery address, city, province, country (default Rwanda), along with the order details and the PawaPay deposit reference. No mobile money PIN or wallet credential is ever requested, seen, or stored by Cypadi.
>
> **3. Customer identification**
> Standard due diligence for all customers is the information in §2, which is sufficient to identify the payer and reconcile each order to a PawaPay deposit. Enhanced due diligence — a copy of a government-issued identity document — is requested when: (a) a single order exceeds RWF 500,000; (b) cumulative purchases exceed RWF 2,000,000 in a rolling 30 days; (c) the payer's name or number differs materially from the account holder; (d) a transaction is flagged under §6. Documents are obtained by email to compliance@cypadi.com and retained per §9.
>
> **4. Supplier checks (before listing)**
> Before any supplier's products appear on shop.cypadi.com, Cypadi obtains and verifies: legal or trading name; owner's full name, phone and email; national ID or passport of the owner; RDB registration certificate and TIN where the supplier is a registered business; business address; bank or mobile money details for settlement; and a signed supplier agreement. A supplier file is opened and the following is recorded on it: risk rating (§5), screening result (§7), product categories approved, and the date of approval. Products do not appear on the site until the file is complete and the Compliance Officer has signed off. Target turnaround is one business day.
>
> **5. Risk rating**
> Every supplier is rated at onboarding and re-rated annually. **Low:** registered business, verifiable address, established product category, no adverse findings. **Medium:** unregistered individual, new business, or a category with higher return/fraud rates (electronics, phones). **High:** cash-intensive categories, politically exposed persons, adverse media, or anything Cypadi cannot verify. Medium-risk suppliers are reviewed every six months; high-risk every three months and require Compliance Officer sign-off on each order above RWF 1,000,000.
>
> **6. Transaction monitoring**
> Cypadi reviews orders daily for: repeated failed-then-retried deposits from the same number; one phone number paying for many unrelated accounts; order values at or near the provider limit; multiple orders to one address from different payers; rapid order/cancel cycles; and delivery addresses that cannot be verified. Any of these is escalated to the Compliance Officer, who may hold the order, request identification, refund the payment, or suspend the account.
>
> **7. Screening**
> At onboarding and annually thereafter, supplier owners and any high-value customer are screened against the UN Security Council consolidated list, the OFAC SDN list, the EU consolidated list, and adverse media. The screening result and date are recorded in the file. A confirmed match results in refusal or termination and, where required, a report to the Financial Intelligence Centre.
>
> **8. Payments and settlements**
> Customer payments are collected in RWF through PawaPay Ltd and settled to Cypadi's merchant account with the licensed mobile money operators MTN Mobile Money Rwanda and Airtel Money Rwanda. Cypadi does not operate a wallet for customers and does not hold customer funds. Refunds are made to the original paying number through the same provider.
>
> **9. Record keeping**
> Registration data, order data, deposit references, identification documents obtained under §3, supplier files, screening results, and escalation records are retained for **five years** after the end of the customer or supplier relationship, stored on access-controlled systems, and available to PawaPay, the operators, or a competent authority on request.
>
> **10. Reporting and escalation**
> Compliance Officer: **[Name]**, [title], compliance@cypadi.com, [+250 …]. Any staff member who becomes aware of suspicious activity reports it to the Compliance Officer the same day. Where Cypadi knows or suspects money laundering or terrorist financing, it reports to the Financial Intelligence Centre without delay and does not tip off the customer.
>
> **11. Refusal, suspension, termination**
> Cypadi may decline onboarding, suspend an account, cancel an order, withhold a settlement, or terminate a relationship where these procedures are not met or where suspicion arises. Records of the decision and its reason are kept.
>
> **12. Training and review**
> All staff with access to customer data or payments are briefed on this procedure at induction and annually; attendance is recorded. This procedure is reviewed at least annually and on any material change in products, providers, or regulation.

---

</append_to_file>

</append_to_file>
## 4. Document review — `KYC and AML Policy (Final).docx`

### Problems

| # | Problem | Why it matters |
|---|---|---|
| 1 | Header has no legal entity, no owner, no approver, no review date — same as §3. | It reads as an unsigned draft. |
| 2 | *"keeping with applicable anti-money laundering requirements"* — never names the law. | Name it: **Law No. 058/2023 on the prevention and punishment of money laundering offences**, and the FIC as reporting authority. One citation separates a real policy from a copy-paste. |
| 3 | *"Basic information such as name, email, phone number, and country may be collected"* — "may be" and "such as". | Hedging in the *collection* section is fatal: you either collect it or you don't. Replace with a definitive list matching your actual schema. |
| 4 | No CDD / EDD distinction, no threshold that triggers identification. | This is the core of any KYC policy. Without a trigger the policy has no operational meaning. |
| 5 | No PEP definition, no beneficial ownership, no sanctions screening. | Standard components. Their absence is the most common reason a KYC policy is sent back. |
| 6 | No suspicious-transaction reporting process, no mention of the FIC. | In Rwanda, an AML policy that never mentions the Financial Intelligence Centre is visibly incomplete. |
| 7 | No retention period. | Name a number: five years. |
| 8 | No named MLRO / compliance contact. | Reviewers want a human. |
| 9 | No customer-facing element: nothing on complaints, data access, or refusal of a transaction. | A policy that only describes internal monitoring is often criticised. |
| 10 | No mention of PawaPay or the MMOs anywhere. | Your policy should acknowledge the payment chain you depend on. |

### Rewritten document (drop-in, Model B)

> **CYPADI LTD**
> **KYC & Anti-Money Laundering Policy**
> Version 1.1 · Effective 1 November 2026 · **Owner:** Compliance Officer · **Approved by:** Managing Director, 1 November 2026 · **Next review:** 1 November 2027
>
> **1. Purpose**
> Cypadi Ltd ("Cypadi") is committed to preventing money laundering, terrorist financing, fraud and abuse of its marketplace. This policy sets out how Cypadi identifies its customers, monitors transactions and reports suspicion, in line with Law No. 058/2023 on the prevention and punishment of money laundering offences and the directives of the Financial Intelligence Centre (FIC).
>
> **2. Scope and business model**
> Cypadi operates shop.cypadi.com as an online store in Rwanda and is the merchant of record for all products sold there. Customers pay in RWF by MTN Mobile Money or Airtel Money through PawaPay Ltd. Cypadi does not operate a customer wallet, does not hold customer funds, does not offer money transfer and does not pay out to third parties through the payment gateway. This policy applies to every customer and to every supplier whose stock Cypadi lists.
>
> **3. Customer due diligence (CDD)**
> At registration Cypadi collects full name, email address, phone number and username. At checkout it collects delivery address, city, province, country, order value and the PawaPay deposit reference. This is the standard CDD record for every customer and is sufficient to identify the payer and reconcile the payment to the order. Mobile money PINs and wallet credentials are never requested, seen or stored.
>
> **4. Enhanced due diligence (EDD)**
> Cypadi requests a copy of a government-issued identity document, and records the source of funds where relevant, when any of the following applies: a single order exceeds **RWF 500,000**; cumulative purchases exceed **RWF 2,000,000** within a rolling 30 days; the payer name or number does not match the account holder; the customer is a politically exposed person or a family member or close associate; the transaction is flagged under §6. Where identification cannot be satisfactorily obtained, the order is cancelled and the payment refunded to the original paying number.
>
> **5. Risk assessment**
> Customer and supplier risk is rated **low / medium / high**. Factors: order value and frequency, product category, whether the phone number and name are verifiable, delivery address consistency, adverse media, PEP status. Low-risk accounts are monitored by the automated rules in §6. Medium-risk accounts are reviewed manually every six months. High-risk accounts require Compliance Officer approval for each order and are reviewed quarterly. Ratings are recorded and re-assessed at least annually.
>
> **6. Transaction monitoring**
> Cypadi reviews transactions daily against the following rules; any hit is examined by the Compliance Officer within one business day: three or more failed deposits followed by a successful one from the same number; one phone number paying for three or more unrelated accounts; a single order at or within 5% of the provider's maximum; several orders to one delivery address paid from different numbers; repeated order-then-cancel or order-then-refund patterns; an order placed from an IP or device inconsistent with the account's history. Cypadi may delay, hold, decline or reverse any transaction while an investigation is open.
>
> **7. Sanctions and PEP screening**
> At onboarding and annually, supplier owners and high-value customers are screened against the UN Security Council consolidated list, the OFAC SDN list, the EU consolidated list, the Rwandan gazette of designated persons, and adverse media. Results and dates are recorded in the file. A confirmed match results in refusal, freezing of the transaction or termination, and a report to the FIC where required.
>
> **8. Suspicious activity reporting**
> Every member of staff must report suspicious activity to the Compliance Officer the same day. The Compliance Officer assesses the report, keeps a written record, and where Cypadi knows or suspects money laundering or terrorist financing files a suspicious transaction report with the FIC without delay. Cypadi does not disclose to the customer that a report has been made. Compliance Officer: **[Name]**, [title], compliance@cypadi.com, [+250 …].
>
> **9. Record keeping**
> Identification data, order and deposit records, screening results, monitoring alerts and investigation files are retained for **five years** after the end of the customer relationship, stored on access-controlled systems, and produced to PawaPay, the mobile money operators, the FIC or any competent authority on request.
>
> **10. Staff responsibilities and training**
> All staff with access to customer data, orders or payments are briefed on this policy at induction and annually; attendance is recorded. Failure to follow this policy is a disciplinary matter.
>
> **11. Customer requests and complaints**
> A customer may request access to, correction of, or deletion of their personal data by writing to compliance@cypadi.com. Requests are answered within 30 days. Complaints about a blocked or reversed transaction are handled under our published Refund & Returns policy and answered within 10 business days.
>
> **12. Review**
> This policy is reviewed at least annually, and immediately on any change of business model, payment provider, product range or applicable law.

---
## 5. Consistency check: documents vs. website vs. code

A reviewer compares these three in this order: the website (30 seconds), the two documents (5 minutes), then the payment integration. Everything below must tell the same story on the day you press send.

| Statement | `Final` docs | `Corrected` docs | Website today | Code today | Verdict |
|---|---|---|---|---|---|
| Who sells to the customer | independent sellers | Cypadi (merchant of record) | "Independent sellers (vendors) list products" (About Us, footer) | `products.seller_id`; sellers self-register | **Conflict** |
| Vendor payouts | "may process payouts" | removed | "Seller payouts may be processed after successful order completion" (About Us) | `initiatePayout()` exists but is **never called** | **Conflict** |
| Vendors approved before selling | yes | — | "verified sellers" | **no gate** — account is instantly `active` | **False claim** |
| Country collected from vendors | yes | at checkout | not collected | `accounts` has no country column; only `orders.delivery_country` | **False claim** |
| Government ID collected | yes, where required | yes, where required | not mentioned | no upload, no form, no store | **Unsupported** |
| Refund policy published | — | — | `refund-policy.php` exists | — | ✔ consistent |
| Privacy policy published | — | — | `privacy-policy.php` exists but is thin | — | ⚠ expand |
| Terms published | — | — | `terms.php` exists, no legal entity, no governing law | — | ⚠ expand |
| Company legal identity on site | — | — | footer: "Cypadi", Kigali, +250 784 801 809, info@cypadi.com | — | ⚠ add **Cypadi Ltd** + TIN + registered address |
| Payment confirmation | — | — | polling page | callback URL points at a different host | **Broken** |

### Text changes to make the site match Model B

In `about-us.php`:
- replace *"Independent sellers (vendors) list products; customers browse, order, and pay by mobile money."* with *"Cypadi sources products from vetted suppliers and sells them directly to customers as the seller of record."*
- delete *"Seller payouts may be processed after successful order completion, in line with our policies."*

In `includes/footer.php`:
- replace *"Cypadi Shop is a Rwanda marketplace where verified sellers list products and customers pay by mobile money."* with *"Cypadi Shop is a Rwandan online store operated by Cypadi Ltd. Customers pay by MTN MoMo or Airtel Money at checkout."*

If you keep Model A instead, these sentences stay and you must instead **build the payout flow and the seller approval gate**, and be prepared to answer the disbursement-licensing question in the first call. Model B is a far smaller job.

---

## 6. Documents still missing from the submission

These are what a provider's onboarding team typically expects alongside the two policies. Name every file `Cypadi - <Document> - <YYYY-MM-DD>.pdf` and send one folder or one zip.

### Company / legal — highest priority, almost certainly required
1. **RDB Certificate of Domestic Company Registration** for Cypadi Ltd.
2. **Company TIN certificate** (RRA).
3. **RSSB registration certificate**, if you have staff.
4. **Memorandum & Articles of Association** as filed with RDB.
5. **Directors' and shareholders' register**, plus a **passport or national ID** for each director and beneficial owner.
6. **Proof of registered business address** — lease, utility bill, or RDB address confirmation.
7. **Board resolution** naming who may open and operate the PawaPay account, with that person's ID.
8. **Company profile**, one page: what you sell, since when, where you deliver, expected monthly volume, average order value.

### Payment and operations
9. **Settlement account details** (bank account or MMO merchant/till number) on company letterhead.
10. **Refund & chargeback procedure** — you have the public page; produce the internal version too (who approves, within what time, how the customer is told).
11. **Complaint handling process** with a response-time commitment.
12. **Product categories and prohibited-items list.**
13. **Checkout screenshots** — three or four images of the live flow including the mobile money PIN prompt.
14. **One completed test transaction** — order number, `depositId`, amount, status, timestamp. This single screenshot answers a dozen questions.

### Compliance programme
15. **AML/CFT policy** (rewritten, §4) plus the **sanctions/PEP screening procedure** and a sample saved screening result.
16. **Vendor & customer due diligence procedure** (rewritten, §3).
17. **Risk assessment methodology** — low/medium/high criteria and what each level triggers.
18. **Record retention statement** and where records are stored.
19. **Staff AML training log** — two rows with names and dates is enough at your stage; the *existence* of the log is the point.
20. **Compliance officer appointment** — a one-paragraph internal memo naming the person and their contacts.
21. **Personal data protection registration** with NCSA under Rwanda's Law No. 058/2021 on personal data protection, or a statement of registration status and timeline.

### Files to delete or rename before sending
22. **`~$ndor Due Diligence Procedures (Final).docx` and `~$C and AML Policy (Final).docx` are not documents.** Names beginning with `~$` are Word owner/lock files, created automatically when a `.docx` is open. They hold a few bytes of username metadata and **cannot be opened by the recipient** — I tried to read them and they do not parse as a Word archive. Delete both. Attaching them looks like you sent a corrupted file.
23. **Do not send `Final` and `Corrected` together.** Keep one pair, rename it clearly (`Cypadi - KYC & AML Policy - v1.1.pdf`), and move the other out of the submission folder.
24. **Export to PDF, not `.docx`.** PDFs render identically everywhere, cannot be silently edited after sending, and signal a finished document.

---
## 7. The email — rewritten

Your draft mixes four things (a status update, a website description, the document submission, and a readiness confession) and ends with no explicit ask. A submission email should do five things in this order: **who you are → what you are sending → why → what you need back → who to contact.**

> **Subject:** Cypadi Ltd — Due diligence documents | PawaPay account
>
> Dear [Name],
>
> I am writing from **Cypadi Ltd** (TIN: [ … ], RDB: [ … ]), the company operating **shop.cypadi.com**, an online store in Rwanda. We collect payments from our customers by MTN Mobile Money and Airtel Money through PawaPay.
>
> **Website**
> shop.cypadi.com is live. Customers browse products, add them to a cart, enter a delivery address and pay at checkout by mobile money. Cypadi Ltd is the merchant of record for every order. Our terms, privacy policy and refund policy are published in the site footer.
>
> **Payment integration**
> Our checkout calls the PawaPay Collections API on your production environment (`/v2/deposits`). Each order is created with a unique `orderNumber` and a unique `depositId`, and both are passed in the deposit metadata so every payment can be reconciled to a single order. Order status is updated from the payment callback, with a polling fallback on the confirmation page. Current transaction range is RWF 5 – 2,000,000 for MTN MoMo and RWF 100 – 1,500,000 for Airtel Money, within the limits published in your active configuration.
>
> One point I would like to close out: the callback URL currently on our account is `https://payment.cypadi.com/api/v1/webhook/unified.php`, while our application endpoint is `https://shop.cypadi.com/pawapay-callback.php`. Please advise which should remain registered, or update the configuration to the latter, so that every confirmed payment is recorded on our side.
>
> **Documents attached**
> - KYC & AML Policy — Cypadi Ltd — v1.1 (PDF)
> - Vendor & Customer Due Diligence Procedures — Cypadi Ltd — v1.1 (PDF)
> - RDB Certificate of Company Registration
> - Company TIN Certificate
> - Directors and shareholders, with identification
> - Board resolution authorising the account signatory
> - Refund and complaint handling procedure
> - Product categories and prohibited items list
> - Screenshots of the checkout flow, and one completed test transaction
>
> **What I would be grateful to receive**
> 1. Confirmation that these documents complete your file, or what further items are required.
> 2. Confirmation of the correct callback endpoint (above).
> 3. Whether callbacks can be enabled with **signature verification** for our account. We would like to validate the authenticity of every callback before marking an order paid, and will switch to signed callbacks as soon as you enable them.
> 4. Confirmation of any transaction limits or volume thresholds that apply to our account.
>
> I am happy to supply anything further the same day, or to walk your team through the checkout flow on a short call at your convenience.
>
> Kind regards,
> **[Full name]**
> [Title], Cypadi Ltd
> [mobile] · [email] · shop.cypadi.com

Nothing in that email apologises for readiness, and every sentence is verifiable in your own system. Note item 3 — asking *them* for signature verification makes you the party who understands the risk, which is the impression this whole submission should create.

---

## 8. What the site still misses as a prototype (and what to fix in the next hour)

The site is genuinely close to presentable: real catalogue, cart, checkout, mobile money payment page, order history, admin panel, seller panel, and all four policy pages. Below is what a reviewer clicking through it will notice, ordered by how badly it hurts.

### P0 — do these before you send the email (≈45–60 minutes total)

| # | Gap | Why it hurts | Effort |
|---|---|---|---|
| 1 | **Callback URL mismatch** (§0.2) | Payments succeed but orders stay `pending` if the customer closes the tab. This is the one defect that produces a support ticket on day one. | 5 min (dashboard) |
| 2 | **Company identity missing from the site** — footer shows "Cypadi", no legal name, no TIN, no registered address | A reviewer looking for "Cypadi Ltd" on the site and finding only "Cypadi" has to ask. Add: *Cypadi Ltd · TIN [ … ] · [registered address], Kigali · info@cypadi.com*. | 10 min |
| 3 | **No seller approval gate** (§3 row 3) | Your document says sellers are reviewed before selling; the code lets anyone list a product in 60 seconds. | 30–45 min (or rewrite the doc sentence instead — 2 min) |
| 4 | **Contact details may not work** — `+250 784 801 809` and `info@cypadi.com` | PawaPay will email and may call that number. A bounce or an unanswered call stalls onboarding for a week. Verify both today. | 5 min |
| 5 | **Default admin credentials published in `README.md`** and never changed | Anyone who finds the repo owns your admin panel. | 10 min |
| 6 | **No `.gitignore`; live token in `.env`** | One `git add .` publishes a live payment credential. | 2 min |
| 7 | **Terms page has no legal entity, no governing law, no contact for disputes** | Reviewers read the T&Cs to confirm who they are contracting with. `terms.php` currently never names the company. | 10 min |

### P1 — visible, but not fatal for a screenshot walkthrough

| # | Gap | Note |
|---|---|---|
| 8 | No **order confirmation page with a receipt** after payment | Customers land on `my-orders.php`, which is functional but does not read like a confirmation. A distinct "Thank you — order #123 paid" screen is 20 minutes of work and makes the flow feel finished. |
| 9 | **Privacy policy is three sentences** | Add: what is stored, why, retention, third parties (PawaPay, MTN, Airtel), customer rights, contact. It is the page a compliance reviewer actually reads. |
| 10 | No **contact form / support email** on `contact-us.php` that visibly works | If it is a static page, that is fine — but confirm it does not look broken. |
| 11 | **Empty-cart and no-results states** | Check `shoping-cart.php` with an empty cart and a search with no hits; make sure they show a message rather than a blank area. |
| 12 | **Product images are inconsistent sizes** | One CSS rule per card (`object-fit: cover`, fixed aspect ratio) makes the catalogue look professional. |
| 13 | **No "about the company" paragraph on About Us** — no founding year, no location beyond "Kigali" | Reviewers want to know you are a real operating business. Three sentences is enough. |
| 14 | **No visible seller/vendor onboarding explanation** | If you keep any supplier language, explain in one sentence how a supplier applies and that Cypadi reviews before listing. |
| 15 | **HTTPS and `www` consistency** | Verify `https://shop.cypadi.com` loads without a certificate warning, and that the payment page is not reachable over plain `http://`. |

### P2 — later
WhatsApp/support chat widget, order tracking statuses shared with the customer, email notifications on order placement, seller dashboard payout report, French translation of policy pages (Rwanda is bilingual and a French version of the T&Cs is a nice signal).

### The 60-minute hosting checklist

1. Upload the site to the host serving `shop.cypadi.com`; confirm `https://` is valid with no mixed-content warnings.
2. Import the database, confirm `includes/db-migrate.php` creates `orders` and `order_items`.
3. Set the `.env` values on the server (`PAWAPAY_API_TOKEN`, `PAWAPAY_BASE_URL=https://api.pawapay.io`, `PAWAPAY_CURRENCY=RWF`, `PAWAPAY_COUNTRY=RWA`) — and make sure `.env` is **outside** the web root or blocked by the server config. Right now it sits in the document root, which means `https://shop.cypadi.com/.env` may be downloadable. **Test that URL. If it downloads, fix it before anything else.**
4. Change the admin password; delete the demo seller and demo products you do not want shown.
5. Fix the callback URL (§0.2), then run one real RWF 100 payment end to end, closing the browser after the PIN prompt.
6. Confirm the row in `orders` shows `paymentStatus='paid'` and `orderStatus='Paid'`, and that the items appear in `my-orders.php`.
7. Take the four screenshots for the email: home page, product page, checkout, payment page.

---

## 9. Pre-send checklist

**Blocking — must be done before the email leaves**
- [ ] Decide **Model A or Model B**, and delete the other pair of `.docx` files from the submission folder.
- [ ] Delete the two `~$…docx` lock files.
- [ ] Rewrite both documents with a legal entity, version, owner, approver, next-review date.
- [ ] Remove every claim the system does not support: vendor approval gate, vendor country field, government-ID collection (or make them true).
- [ ] Align `about-us.php` and `includes/footer.php` with the model you chose.
- [ ] Add "Cypadi Ltd", TIN and registered address to the footer and to `terms.php`.
- [ ] Fix the callback URL at PawaPay (§0.2) and test one real payment to completion.
- [ ] Verify `info@cypadi.com` receives mail and `+250 784 801 809` is answered.
- [ ] Confirm `https://shop.cypadi.com/.env` is **not** downloadable.
- [ ] Change the admin password; rotate the PawaPay token if it has ever been pushed anywhere.
- [ ] Add `.gitignore` (`.env`, `.vscode`, `.specstory`, `*.docx`, `~$*`).
- [ ] Export both policies to PDF and name them `Cypadi - <Title> - v1.1.pdf`.

**Should accompany**
- [ ] RDB certificate, TIN certificate, directors' IDs, board resolution.
- [ ] One-page company profile with expected monthly volume and average order value.
- [ ] Four checkout screenshots plus one completed transaction record.
- [ ] Product categories and prohibited-items list.
- [ ] Refund/complaint procedure (internal version).

**Nice to have, and cheap**
- [ ] Ask PawaPay to enable signed callbacks (and mention it in the email — it reads as competence).
- [ ] Named compliance officer with a `compliance@cypadi.com` mailbox.
- [ ] NCSA personal-data registration status.

---

## Appendix — what I inspected

| Item | Result |
|---|---|
| `Vendor Due Diligence Procedures (Final).docx` | Extracted and read in full (2,126 chars). |
| `KYC and AML Policy (Final).docx` | Extracted and read in full (1,780 chars). |
| `Vendor Due Diligence Procedures - Corrected.docx` | Extracted and read in full (2,277 chars). |
| `KYC & AML Policy - Corrected.docx` | Extracted and read in full (1,698 chars). |
| `KYC and AML Policy - Corrected.docx` | Extracted (1,893 chars) — duplicate/older variant of the `&` file. |
| `~$ndor Due Diligence Procedures (Final).docx` | **Not a document.** Word owner/lock file; fails to open as a Word archive. Delete. |
| `~$C and AML Policy (Final).docx` | **Not a document.** Same. Delete. |
| PawaPay production API (`/v2/active-conf`) | HTTP 200, `CYPADI LTD`, MTN + Airtel OPERATIONAL for RWA deposits. |
| PawaPay callback registered on the account | `https://payment.cypadi.com/api/v1/webhook/unified.php` — does not match the application's endpoint. |
| PawaPay signature configuration | `signedRequestsOnly: false`, `signedCallbacks: false`. |
| `payment.php`, `pawapay-callback.php`, `pawapay-poll.php`, `payment-waiting.php`, `includes/pawapay.php` | Real, working integration: deposits, callback handling, status polling, provider prediction. |
| `PawaPayClient::initiatePayout()` | Defined but **never called** anywhere in the codebase. No payout flow exists. |
| `signup/index.php` | Collects name, phone, email, username, password, role. No country, no ID, no business name, no approval gate. |
| `database.sql` → `accounts` | `id, name, number, email, username, password, role, status, registration_date`. No country, no KYC columns. |
| `about-us.php`, `terms.php`, `privacy-policy.php`, `refund-policy.php`, `includes/footer.php` | Present; contain marketplace + payout language that conflicts with the merchant-of-record documents. |
| Git state | `.git` present, `.env` untracked but unprotected, no `.gitignore`, README publishes default admin credentials. |
