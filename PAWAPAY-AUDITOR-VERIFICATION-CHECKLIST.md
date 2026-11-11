# What a PawaPay reviewer will actually check — and in what order

**Site under review:** `shop.cypadi.com` (this repository)
**Prepared:** 10 November 2026
**Purpose:** what an onboarding analyst or approver at PawaPay can verify about Cypadi before approving the account, ranked by how likely they are to check it, with the honest state of each item today.

---

## How to read this

A PawaPay reviewer is not an auditor with a statutory power. They are an analyst with a queue, a checklist and about twenty minutes. Their job is to answer one question: *is this a real business that will not generate chargebacks, fraud reports or regulatory attention?*

That changes what matters. They will not read your two policies line by line. They will look at three things in this order:

1. **Can I confirm this company exists?** (registries, domain, web presence)
2. **Does the website look like a business a customer would actually pay?** (60 seconds of clicking)
3. **Does the money flow match what they told me?** (test transaction)

Everything below is sorted that way. Where the site currently fails a step, it says so plainly and gives the fix.

---

## Tier 1 — verified from public sources, before they ever click your site

This is the first five minutes. None of it requires your involvement, and you cannot intervene once they start.

### 1.1 The RDB registration number

They already hold the certificate from onboarding. What they do now is check that the number in your email, in your policies and on the certificate is the same number, and that the company name matches exactly: **CYPADI LTD**, not "Cypadi", not "Cypadi Shop".

| Where the number should appear | State today |
|---|---|
| RDB certificate (already on file) | ✔ 123088034 |
| The two signed PDFs | ✔ 123088034 |
| The reply email | ✔ quoted |
| **The website footer** | ✘ **absent** |

**This is the single most likely finding against you.** The footer says `Cypadi Ltd · Kigali, Rwanda` and nothing more. A reviewer who has just read "RDB: 123088034" in your letter and then scrolls to the bottom of `shop.cypadi.com` and finds no number at all writes one line in their notes: *"website does not identify the legal entity."*

**Fix:** `includes/footer.php`, the "About Cypadi" block — change

```
<p class="mt-2">Cypadi Ltd &middot; Kigali, Rwanda</p>
```

to

```
<p class="mt-2">Cypadi Ltd &middot; RDB 123088034 &middot; TIN [your TIN]</p>
<p>Kimironko, Gasabo, Kigali, Rwanda</p>
```

You hold the RRA certificate; the TIN is on it. This is a ten-minute change and it closes the gap.

### 1.2 The TIN, against the RRA certificate they already have

They will not phone the RRA. They will check that the TIN in your documents is the same TIN, belongs to CYPADI LTD, and is not obviously invented. Same fix as above: put the real TIN on the site.

### 1.3 The domain

`cypadi.com` and `shop.cypadi.com`. They may run a WHOIS. What they are looking for:

- Registration date consistent with a company registered in 2026 — fine if so, suspicious only if the domain is *older* than the company and registered to someone else.
- Registrant not hidden behind a privacy service that belongs to a different country.
- A valid TLS certificate on `shop.cypadi.com` with no browser warning.

**Fix if needed:** confirm the certificate is valid and `http://` redirects to `https://`. A certificate warning at this stage reads as an abandoned project.

### 1.4 Whether the company has any independent footprint

They will search **"Cypadi"**, **"Cypadi Ltd Rwanda"**, **"shop.cypadi.com"**, and possibly the phone number.

Today, a new company with a new domain returns almost nothing. That is not fatal — they know you are new — but it is a reason to look harder at everything else. What moves the needle cheaply:

- A Google Business Profile for Cypadi Ltd at the Kimironko address.
- A public Facebook or Instagram page with the same logo and a few product posts.
- The company listed in any Rwandan business directory.

None of this is a PawaPay requirement. All of it converts "unverifiable new entity" into "new entity with a footprint", and the second one gets approved faster.

### 1.5 Whether the documents themselves are consistent with each other

They have the two PDFs. They will notice contradictions between them. Right now the pair is internally consistent — merchant of record, no third-party payouts, same RDB, same address, same signatory.

**But check one thing yourself before you send:** the signature block in both PDFs gives **+250 792 359 800**. The website, the footer, the About page, the Contact page and every seller page give **+250 784 801 809**. Two different phone numbers, one company, visible in a single side-by-side.

A reviewer who tries the number on the letter and gets no answer, then sees a different number on the site, has found a real inconsistency. **Decide which number is the company's and use only that one** in all four places.
---

## Tier 2 — what they see in the first sixty seconds on the site

They open `shop.cypadi.com` on a desktop and scroll. This is where most submissions are decided, and it is entirely about what is on the screen.

### 2.1 Does the shop look like a real shop?

**State today: broadly yes, with three visible flaws.**

What works: a real catalogue with categories, prices, product images, a working cart, a wishlist, a search bar, login, and a footer with four policy pages. That is more than many applicants have.

What a careful reviewer notices:

| # | What they see | Why it matters | Fix |
|---|---|---|---|
| 1 | **Dollar signs on the homepage.** Products render as `$25.00` while the header, the cart and the checkout say `RWF`. | A Rwandan store pricing in dollars on its own front page looks like an unedited template. It also contradicts the Terms, which state prices are in RWF. | `index.php` prints `$<?php echo $price_current; ?>` in two places. Replace with `RWF <?php echo number_format($price_current); ?>`. |
| 2 | **The search box does nothing.** It is `<form action="#">` with no handler. Typing a product name and pressing SEARCH reloads the page and shows nothing. | A reviewer testing the catalogue hits a dead control within ten seconds. | Either wire it to `category.php` with a query parameter, or remove the box. A missing search is better than a broken one. |
| 3 | **Four social icons link to `#`.** Facebook, Twitter, LinkedIn, Pinterest — all dead. | Reads as a template that was never finished. | Remove them, or point them at real profiles. Removing is faster and looks more deliberate. |
| 4 | "16 Products found" is hardcoded while the grid renders 15. | Petty, but it is the kind of detail a reviewer notices *after* they have already formed an opinion. | Count the rows, or drop the line. |

### 2.2 Is the legal entity on the page?

Covered in Tier 1.1 — the footer is the first place they look, and today it is missing the RDB number, the TIN and the registered address. Fix that one block and this whole subsection becomes a pass.

### 2.3 Do the policy pages hold up to a thirty-second read?

A reviewer clicks Terms, Privacy, Refund. They are not reading for legal quality; they are checking that the pages exist, name the company, and do not contradict the email.

| Page | State today | Verdict |
|---|---|---|
| `terms.php` | 11 sections, names Cypadi Ltd, states Cypadi is seller of record, prices in RWF, refunds to the original paying number, governing law Rwanda, complaints to info@cypadi.com within 10 business days. | **Strong.** Directly supports the KYC policy. |
| `privacy-policy.php` | 9 sections, names the data controller, lists what is collected, names PawaPay / MTN / Airtel, five-year retention, NCSA as the complaints authority. | **Strong.** Consistent with the KYC policy's retention clause. |
| `refund-policy.php` | **Two sentences.** "Contact info@cypadi.com within 7 days… Approved refunds are processed via the original payment method where possible." | **Weak.** The thinnest page on the site, and the one a payment provider cares about most. |
| `contact-us.php` | Phone, email, address line. No form. | Acceptable, but thin. |

**The refund page is the one to fix.** "Where possible" is a hedge a reviewer reads as "sometimes we won't". The Terms already promise more than this page does. Expand it to state: the 7-day window, what qualifies (damaged, defective, wrong item), that refunds go to the original paying number, the processing time, and who approves. Half a page is enough. The internal version already described in the review pack is the text to use.

### 2.4 Does the About page match the documents?

`about-us.php` was rewritten to the merchant-of-record model: Cypadi sources from vetted suppliers, sells as seller of record, verifies business name, country, owner's identity document and categories before listing, and does not publish products from unverified suppliers.

**That is exactly what the VDD procedures document says.** This alignment is worth more than it looks — it is the thing a reviewer cross-checks first, because inconsistency between the site and the paperwork is the commonest reason a small merchant is sent back.

### 2.5 Does the source code give anything away?

A technical reviewer — and PawaPay has them — will view source or open DevTools. Two things are visible today:

- The homepage `<meta name="description">` reads **"Ogani Template"** and the keywords read **"Ogani, unica, creative, html"**. That is the name of the purchased theme, left in.
- The page `<title>` tags are fine (`Cypadi Shop | Home`).

**Fix:** replace the description with a real one — *"Cypadi Shop — buy online in Rwanda. Pay with MTN MoMo or Airtel Money."* — and delete the keywords line. Thirty seconds, and it removes the only "this is a template" signal in the markup.
