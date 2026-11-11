# What a PawaPay approver can check about you from the internet

**Subject:** `shop.cypadi.com` — Cypadi Ltd, RDB 123088034
**Prepared:** 11 November 2026
**Status at time of writing:** `shop.cypadi.com` **does not resolve** (verified: `dns.google` returns *Non-existent domain*). `cypadi.com` resolves to `66.29.148.173` and `www.cypadi.com` is a CNAME to it. The subdomain is not created yet.

---

## How this document is ordered, and why

The previous checklist ranked findings by *severity*. This one ranks them by **probability that the reviewer performs the check at all**.

That distinction matters. An approver at a payment company has a queue and roughly twenty minutes. He does not read your policy pack line by line — he already has it. What he does is **five mechanical actions**, in this order:

| # | What he does | Takes | Can you influence it once he starts? |
|---|---|---|---|
| 1 | Types `shop.cypadi.com` into a browser | 3 sec | No |
| 2 | Googles **"Cypadi"** and **"Cypadi Ltd Rwanda"** | 30 sec | No |
| 3 | Scrolls your homepage without clicking anything | 60 sec | No |
| 4 | Clicks Terms, Refund, About, Contact | 90 sec | No |
| 5 | Tries the obvious technical probes — `/admin`, `/.env`, `/.git`, the credentials in a public repo | 3 min | No |

Everything below is sorted into those five actions, then into the rarer checks (registry, WHOIS, DNS records, Wayback) that he only reaches if something in steps 1–5 made him curious.

**The single most likely check is #1, and today it fails absolutely.** So start there.

---

## CHECK 1 — He types the domain

**Probability he does this: 100%. It is the first thing anyone does.**
**Today: total failure — the host does not exist in DNS.**

```
$ nslookup shop.cypadi.com 8.8.8.8
*** dns.google can't find shop.cypadi.com: Non-existent domain

$ nslookup cypadi.com 8.8.8.8
Name:    cypadi.com
Address: 66.29.148.173
```

A browser pointed at `shop.cypadi.com` shows **"This site can't be reached — DNS_PROBE_FINISHED_NXDOMAIN"**. Not a 404, not a blank page: a DNS failure, which is the one error that says *this address was never set up*, not *this site is broken*.

You have said you will host it, so the job is to make sure that when you do, the reviewer's 3 seconds end well. Three things decide that, and all three are visible before any page loads:

| What he sees | Pass looks like | Fail looks like |
|---|---|---|
| `http://` | 301 redirects to `https://` | The page loads over plain HTTP — instantly flags a payment site |
| The certificate | Valid, issued to `shop.cypadi.com` (or `*.cypadi.com`), no warning | `NET::ERR_CERT_COMMON_NAME_INVALID` or a self-signed warning — reads as abandoned |
| `www.shop.cypadi.com` | Resolves, or cleanly redirects | Times out — a reviewer who types `www` out of habit concludes the site is half-configured |

**Minimum bar to pass Check 1:** `https://shop.cypadi.com/` returns the catalogue, `http://` redirects to it, the certificate is valid and auto-renewing (Let's Encrypt via your host's panel is fine), and `www.` behaves. Everything else in this document is worthless until that is true.

> **Do not send the submission email before this works.** Right now the email says the site is live and hands them a URL that does not exist. That is worse than saying nothing, because it is checkable in three seconds and it is wrong.

---

## CHECK 2 — He googles "Cypadi"

**Probability he does this: very high. It is the standard sanity step after opening the site.**
**Today: this search finds your source code, your signed policy PDFs, your signature and stamp images, and a written critique of your own submission.**

This is the most serious finding in this document, and it is not on your website at all.

`https://github.com/M-I-Theogene/php-mysql-marketplace` is **public** and indexed. It is the working repository for this project, and it contains far more than source code:

| What is public in that repo | Why it is a problem |
|---|---|
| `pawapay-documents/Cypadi Ltd - KYC and AML Policy - CYP-AML-001 v1.1.pdf` | The exact signed PDFs you are submitting, downloadable by anyone |
| `pawapay-documents/Cypadi Ltd - Vendor Due Diligence Procedures - CYP-VDD-001 v1.1.pdf` | Same |
| `pawapay-documents/CYPADI Ltd.pdf` | Your company document, publicly downloadable |
| `pawapay-documents/sign.png` | **The signature image used to sign the PDFs.** Once this is public, anyone can place that signature on any document. In a KYC context this is close to disqualifying — it means your signed paperwork is not evidence of anything. |
| `pawapay-documents/stamp.png` | **The company stamp image.** Same problem. |
| `pawapay-documents/_preview/*.png` | Page-by-page renders of both policies |
| `PAWAPAY_SUBMISSION_REVIEW.md` | A candid internal review of your own submission, including the sentence *"the sentence … is false, and it damages you"* |
| `PAWAPAY-AUDITOR-VERIFICATION-CHECKLIST.md` | The earlier version of this very document — a list of your own weaknesses, ranked |
| `KYC-VDD-AUDIT-AND-REPLY.md` | Your drafted reply to PawaPay |
| `database.sql` | Full schema **plus seeded accounts, including the admin username and password hash** |
| `README.md` | Publishes the default login: admin `iqbolshoh`, password `IQBOLSHOH` |
| `config.php` | Database name, plus the password-hashing pepper (`iqbolshoh`) in plaintext |
| `_tmp_*.py`, `_tmp_*.ps1`, `_tmp_*.txt` | ~60 working scripts and text dumps, including the full extracted text of both policy versions |
| `current_ai-progress.txt` | Your own notes to yourself about what to change |

The credentials in `README.md` and `database.sql` are consistent with each other — the seeded admin row hashes `IQBOLSHOH` with the pepper in `config.php`. So the exposure is not hypothetical: **username `iqbolshoh`, password `IQBOLSHOH`** is a working pair against the default install, and it is published.

The reputational half is worse than the technical half. A reviewer who finds `PAWAPAY_SUBMISSION_REVIEW.md` is reading an assessment of *his own queue item* written from your side — with your weaknesses, your callback mismatch, and your own admission that a drafted sentence was false. Whatever he concludes, he will not conclude that you are a careful operator.

### What to do, in order

1. **Make the repository private today** (GitHub → Settings → Danger Zone → Change visibility). Do this before the email goes, not after.
2. **Rotate anything the repo ever exposed**: the PawaPay API token (`.env` was gitignored but treat it as exposed), the admin password, and any database password that appears in a file you pushed.
3. **Assume the signature and stamp are compromised.** If PawaPay ever questions a signed document, you need to be able to say the images were withdrawn on a known date. Re-sign future documents with a fresh signature image, and stop storing signature/stamp PNGs anywhere near a web root or a repo.
4. **Search the repo history, not just the current tree.** Deleting a file in a new commit leaves it in earlier commits. If `pawapay-documents/` was ever committed, making the repo private is the only complete fix without rewriting history.
5. **Do not delete the audit notes — they are useful to you.** Move them out of the repo into a local folder, or into a single private `.md` that is gitignored.

### The unpleasant but necessary question

An approver can also search **"Cypadi"** with no other filter. Today that returns essentially nothing of yours except this repository. So the search leads him *straight* to the one artefact you least want him to see. Making it private converts a search that finds your signature image into a search that finds nothing — which is normal for a new company and raises no question at all.

### Consequence for the rest of this document

The repository's current publicity changes how several later items should be read: any file that is in the repo is already public regardless of what `.htaccess` does on the server. So `.env` protection, `database.sql` blocking and `*.md` blocking are all **necessary but not sufficient** — the content is already out.

---

## CHECK 3 — He scrolls the homepage for sixty seconds

**Probability: 100% once the domain resolves.**
**Today: the site is presentable, but carries four visible "unfinished template" signals.**

This is the highest-value sixty seconds you control. The reviewer is not reading — he is forming an impression of whether this is a shop that takes money from real people. What he registers, in the order it appears:

| Position | What he sees today | What it tells him | Fix |
|---|---|---|---|
| Product grid | **`$25.00`, `$18.00`** — dollar signs on every product | A Rwandan store pricing in USD on its own front page is an unedited theme. It also contradicts your Terms, which state prices are in RWF, and the header/cart, which say `RWF`. | `index.php` lines 120 and 186 print `$<?php echo $price_current; ?>`. Change to `RWF <?php echo number_format($price_current); ?>`. |
| Search bar | A working-looking search box with a `SEARCH` button | He types something. **Nothing happens** — it is `<form action="#">` with no handler, so the page reloads and the box clears. A dead control in the first viewport is worse than a missing one. | Point it at `category.php?search=…`, or delete the form. Deleting is 2 minutes and looks deliberate. |
| Top-right social icons | Facebook, Twitter, LinkedIn, Pinterest | He may click one. All four are `href="#"`, so they jump to the top of the page. | Remove the block, or link real profiles. |
| Footer | `Cypadi Ltd · Kigali, Rwanda` | **The legal entity is not identifiable.** No RDB number, no TIN, no registered address. This is the first place an approver looks to confirm *who* he is onboarding. | Change to `Cypadi Ltd · RDB 123088034 · TIN [yours]` plus `Kimironko, Gasabo, Kigali, Rwanda`. Ten minutes; see Check 4. |
| Below the grid | `16 Products found` above a grid of 15 | A hardcoded count. He will not count, but the page looks machine-generated rather than curated. | Compute it, or drop the line. |
| Page source | `<meta name="description" content="Ogani Template">` and `keywords: "Ogani, unica, creative, html"` | **A technical reviewer will open DevTools or View Source.** "Ogani" is the purchased theme. It is the only place in the live markup that says "template". | Replace with a real description — *"Cypadi Shop — buy online in Rwanda. Pay with MTN MoMo or Airtel Money."* — and delete the keywords line. Thirty seconds. |

### What is working, and worth knowing

The catalogue, categories, product pages, cart, wishlist, login and the four policy pages are all real and functional. That is genuinely more than most applicants at this stage have, and it is what carries the sixty seconds. The four items above are the only things standing between "looks like a real shop" and "looks like a real shop with four rough edges".

### A note on the product images and catalogue

A reviewer forming an impression will also notice whether the catalogue looks like a Rwandan shop. Imported demo products with foreign pricing, stock photography at inconsistent aspect ratios, or categories that do not correspond to what Cypadi actually sells all read the same way: *this is a demonstration, not a business.* Before he looks, confirm that every product listed is (a) something you will actually supply, (b) priced in RWF, and (c) illustrated with an image of the real item or a representative one at consistent size.

---

## CHECK 4 — He clicks Terms, Refund, About, Contact

**Probability: high. This is where he checks that the website and the paperwork tell the same story.**
**Today: two pages are strong, one is thin, and the contact details contradict the PDFs he already holds.**

He is not assessing legal drafting. He is checking four things: does the page exist, does it name **Cypadi Ltd**, does it mention **PawaPay / MTN / Airtel**, and does it say anything that conflicts with the two PDFs in his file.

| Page | State today | Verdict |
|---|---|---|
| `terms.php` | 11 sections. Names Cypadi Ltd, states Cypadi is seller of record, prices in RWF, payment via PawaPay + licensed MMOs, never asks for the PIN, refunds to the original paying number, governing law Rwanda, complaints to info@cypadi.com within 10 business days, prohibited-items sentence in §6. | **Strong.** This is the single best page on the site and it directly supports the merchant-of-record model in the VDD document. |
| `privacy-policy.php` | 9 sections. Data controller named, what is collected, PawaPay/MTN/Airtel named as recipients, five-year retention, NCSA named as the complaints authority, cookies described. | **Strong.** Internally consistent with the KYC policy's retention clause. |
| `about-us.php` | Merchant-of-record description, supplier verification paragraph naming business name / country / owner ID / categories, delivery, data, contact. | **Good.** It matches the VDD procedures almost sentence for sentence, which is exactly what he cross-checks. |
| `refund-policy.php` | **Two sentences:** *"Contact info@cypadi.com within 7 days of delivery for damaged, defective, or incorrect items. Approved refunds are processed via the original payment method where possible."* | **The weakest page, on the topic a payment provider cares about most.** "Where possible" reads as "sometimes we won't". Your Terms promise more than this page does — an inconsistency inside your own site. |
| `contact-us.php` | Three lines: city, phone, email. No form. | **Thin but not broken.** Acceptable if the phone is answered. |

### The refund page — expand it

Half a page is enough. State: the 7-day window and what starts it; what qualifies (damaged, defective, wrong item, not delivered); that refunds go to the **original paying mobile money number**, not a different one; the processing time you will actually meet; and who approves it. The Terms already say most of this — the refund page should not say less than the Terms do.

### The cross-check that will actually bite you

He has the two PDFs open while he reads these pages. Two discrepancies are visible side by side:

**1. The phone number.** Every page of the site — footer, header, About, Contact, Terms, Privacy — gives **+250 784 801 809**. The signature block on the submitted letter gives **+250 792 359 800**. Two numbers, one company, one click apart. If he calls the one on the letter and it is not answered, and then sees a different number on the site, he has found a genuine inconsistency between the paperwork and the business.

**Decide which number is Cypadi's and use it in both places.** If both are real — one for the MD, one for the shop — say so explicitly on the Contact page: *"Customer support: +250 784 801 809. Company secretary: +250 792 359 800."* An explained second number is fine. An unexplained one is a finding.

**2. The legal identity.** He has RDB 123088034 on the certificate. The site's footer says `Cypadi Ltd · Kigali, Rwanda` and nothing else. He is looking for the number to appear somewhere a customer could see it. It does not appear anywhere on the site — not in the footer, not in Terms §1, not in About. Add it in both places:

```
Cypadi Ltd · RDB 123088034 · TIN [your TIN]
Kimironko, Gasabo, Kigali, Rwanda
```

You hold the RRA certificate; the TIN is printed on it. This is the cheapest single credibility gain available to you and it closes the most likely finding.

---

## CHECK 5 — He pokes at the technical side

**Probability: high, but only if he is the technical reviewer rather than the relationship manager.** PawaPay has both.
**Today: several of these probes return something.**

An experienced reviewer runs a short, standard list against any merchant site. Each of these takes him under ten seconds, and each answer tells him something about how the business is run.

### 5.1 The admin panel

He will try `/admin/`. If a login form appears, he will try the credentials that the internet says belong to this codebase — `iqbolshoh` / `IQBOLSHOH`, published in the public `README.md` and consistent with the seeded row in the public `database.sql`.

| Test | What passing looks like |
|---|---|
| `https://shop.cypadi.com/admin/` | A login form, and the published pair **does not work** |
| The login page itself | No default credentials visible on screen, no demo hints |
| `/seller/` and `/signup/` | Reachable and behaving; no PHP warnings |

**Do this before you host:** change the admin username and password. Not just the password — the username `iqbolshoh` is itself the signal that the install was never customised, and it is an Uzbek name on a Rwandan company's store. Create your own admin account, delete or rename the seeded one, and confirm the published pair fails.

Also check: the seeded admin row carries phone `+998997799333` and email `Iqbolshoh@gmail.com`. If that account is still live when he looks at the user list, you are a Rwandan company administered from abroad by a stranger's credentials. Remove it.

### 5.2 The file paths he will try

These are one-line URL tests. The `.htaccess` in the web root currently blocks `.env`, `.htaccess`, `.git` (as a filename), `*.docx`, `*.sql` and `*.md`. It does **not** block everything that matters:

| URL to test after hosting | Blocked today? | Why it matters |
|---|---|---|
| `https://shop.cypadi.com/.env` | Yes — `FilesMatch "^\.(env\|…)"` | Would expose the live PawaPay token |
| `https://shop.cypadi.com/.git/config` | **No.** `FilesMatch` matches the *file name* (`config`), not the folder — so the contents of `.git/` are not covered | Exposes the remote URL, and therefore the GitHub repository, and therefore everything in Check 2 |
| `https://shop.cypadi.com/pawapay-documents/…pdf` | **No — `.pdf` is not blocked** | Your signed policies become downloadable from your own domain |
| `https://shop.cypadi.com/pawapay-documents/sign.png` | **No — `.png` is not blocked** | Your signature image becomes downloadable from your own domain |
| `https://shop.cypadi.com/pawapay-documents/stamp.png` | **No** | Same for the company stamp |
| `https://shop.cypadi.com/database.sql` | Yes | Schema + seeded credentials |
| `https://shop.cypadi.com/README.md` | Yes | Default login |
| `https://shop.cypadi.com/_tmp_check_env.ps1` | **No** | Internal tooling on a public web root |
| `https://shop.cypadi.com/current_ai-progress.txt` | **No** | Your working notes |

**The correct fix is not a longer `.htaccess`.** It is: **do not upload any of it.** Deploy only the files the application actually serves — the `.php` pages, `includes/`, `admin/`, `seller/`, `signup/`, `login/`, `logout/`, `src/`, `assets/`. Never upload `.env`, `.git`, `.vscode`, `_tmp_*`, `_`, `pawapay-documents/`, `*.docx`, `*.md`, `*.sql`, `database.sql`, `current_ai-progress.txt`.

Then, as a second layer, extend the root `.htaccess`:

```apache
# Block version control and editor directories entirely, not just by file name.
RedirectMatch 404 /\.git
RedirectMatch 404 /\.vscode
RedirectMatch 404 /\.specstory

# Block the document folder and any stray PDF/PNG inside it.
RedirectMatch 404 /pawapay-documents
RedirectMatch 404 /_preview

# Block working files.
<FilesMatch "(^_tmp|_progress\.txt$|\.docx$|\.sql$|\.md$|\.ps1$|\.py$)">
    Require all denied
</FilesMatch>
```

Test every row of the table above **after** hosting, with a real browser, not with a guess.

### 5.3 The PHP surface

Two things a reviewer notices immediately if they are wrong, and both are cheap to check yourself by simply loading every page once:

- **Warnings or notices printed into the page.** `config.php` has `die("ERROR: " . $this->conn->error)` on a failed query — any SQL error puts the raw MySQL message, and possibly the query, on screen. Load every page (home, category, product, cart, checkout, payment, my-orders, admin, seller, signup) once with errors visible and fix anything that prints.
- **`display_errors` on in production.** It should be off, with `log_errors` on. A PawaPay reviewer seeing a stack trace on a payment page concludes the integration is unmonitored.

### 5.4 The security headers

He may run the domain through a header check (SecurityHeaders.com or similar). For a payments merchant the expectations are modest but real: `Strict-Transport-Security`, `X-Content-Type-Options: nosniff`, `X-Frame-Options` or a CSP `frame-ancestors`, and no `X-Powered-By: PHP/x.y.z` advertising an outdated version. None of these are in place today. They are four lines in `.htaccess` or the vhost, and their absence is not fatal — but their presence is one more signal that somebody technical is looking after the site.

---

## CHECK 6 — He looks you up in the public record

**Probability: moderate — he reaches these only if Checks 1–5 left him unsure, or if he is thorough.**
**Today: everything here is either unverifiable or absent, which is the risk.**

These are the checks that do not involve your website at all. They matter because they are the ones you cannot fix after the fact, and because a reviewer who finds you in none of them has to rely entirely on the impression from Checks 3 and 4.

### 6.1 The RDB registration number

He holds the certificate. What he checks is that **123088034** appears somewhere he can see it independently, attached to the name **CYPADI LTD** exactly. Rwanda's registry is searchable online, so a determined reviewer can confirm the entity exists; what he cannot confirm is that the website he is about to approve belongs to that entity, because the site never states the number.

That gap is the whole point of Check 4's footer fix. It is a ten-minute change and it closes the one finding an approver can write in a single sentence: *"website does not identify the legal entity."*

### 6.2 The TIN

Same logic. He will check that the TIN in your documents belongs to CYPADI LTD and is a real RRA format, not invented. He will not phone the RRA for a merchant of your size. Put the real TIN on the site and this check costs him nothing.

### 6.3 The domain — WHOIS

`cypadi.com` is the parent; `shop.cypadi.com` is a subdomain and has no separate WHOIS record. He may look up the registered domain:

| What he sees | Reading |
|---|---|
| Registration date in 2026, registrant CYPADI LTD or its director, Rwandan address | Fine — a new company with a new domain is expected |
| Registrant hidden by a privacy service in another country | Neutral on its own, but it removes your only proof of ownership |
| **A "created" date older than the company, registered to somebody else** | Suspicious — suggests a purchased or borrowed identity |

Worth checking yourself: `whois cypadi.com` and confirm the registrant details are the ones you would be happy for PawaPay to read. The hosting IP `66.29.148.173` is a Namecheap shared-hosting address, which is unremarkable.

### 6.4 Email credibility on cypadi.com

He will email `info@cypadi.com`, and if he is technical he may look at the domain's mail records. Three things matter:

- **MX records exist** and point at a real mailbox provider — otherwise `info@cypadi.com` bounces and the whole submission stalls for a week.
- **SPF and DMARC are published** — a domain with no SPF/DMARC sends mail that lands in spam, and some reviewers treat that as a sign the business is not really operating.
- **The address actually receives mail.** Send a test from an external account and confirm a reply within a day. This is the cheapest check on this list and the most embarrassing to fail.

### 6.5 The phone numbers

Every number on the site is **+250 784 801 809**; the letter's signature block gives **+250 792 359 800** (see Check 4). Both are MTN Rwanda prefixes and both are plausible. The reviewer's test is simply: does one of them answer, and does the person who answers know what Cypadi Shop is and that a PawaPay application is in progress?

Brief whoever answers. A confused "hello?" to a compliance analyst is a much larger problem than a missing RDB number.

### 6.6 Wayback Machine and search indexing

`web.archive.org` will show him what your domain looked like in the past, and Google's cache will show him the current index. Two consequences:

- If the site is ever reachable in a half-finished state — the Ogani template with `$` prices and a dead search bar — an archive snapshot preserves that state permanently, even after you fix it. **Host the final version, not an intermediate one.**
- If nothing is indexed at all, that is normal for a new domain and not a finding. He knows you are new.

### 6.7 Independent footprint

Search **"Cypadi"**, **"Cypadi Ltd Rwanda"**, **"shop.cypadi.com"**, and the phone number, from a browser you are not logged into. Today the results are near-empty, and the only substantive hit is the GitHub repository in Check 2.

A new company with no footprint is not disqualified — but it removes every corroborating signal, and it makes Checks 3 and 4 carry the whole decision. The cheapest ways to create one, in order of value:

1. **A Google Business Profile** for Cypadi Ltd at the Kimironko address, with the phone number, hours and a few photographs.
2. **A Facebook or Instagram page** using the same logo, with a handful of real product posts.
3. **A listing in a Rwandan business directory** that shows the RDB number.
4. **A single news, directory or marketplace mention** that ties the name to the address.

None of this is a PawaPay requirement. All of it converts "unverifiable new entity" into "new entity with a footprint", and the second is approved faster.

---

## The master table

Every check, its probability, its state today, and what it costs to fix — in the order an approver encounters them.

| # | He checks | Probability | State today | Risk | Fix effort |
|---|---|---|---|---|---|
| 1 | `shop.cypadi.com` resolves over HTTPS | **100%** | **NXDOMAIN — does not exist** | **Fatal** | Host it; TLS + redirect |
| 2 | Google "Cypadi" | **Very high** | **Public GitHub repo: signed PDFs, signature and stamp images, audit notes, admin password** | **Fatal** | Make repo private (~2 min) |
| 3 | Homepage, first viewport | 100% | `$` prices, dead search bar, dead social icons | High | ~30 min |
| 4 | Footer legal identity | High | `Cypadi Ltd · Kigali, Rwanda` — no RDB, no TIN | **High** | 10 min |
| 5 | Refund policy | High | Two sentences; weaker than your own Terms | High | 30 min |
| 6 | `/admin/` with published creds | High (technical reviewer) | Seeded `iqbolshoh` / `IQBOLSHOH` still in the database | **High** | 15 min |
| 7 | `/.git/config`, `/pawapay-documents/*.pdf`, `sign.png` | High (technical reviewer) | Not blocked by the current `.htaccess` | **High** | Don't upload them; 10 min |
| 8 | View Source / meta description | Medium | `"Ogani Template"`, `keywords: Ogani, unica` | Medium | 1 min |
| 9 | Phone number consistency | Medium | Site `784 801 809` vs letter `792 359 800` | Medium | Pick one; 5 min |
| 10 | `info@cypadi.com` receives mail | Medium | Unverified — must be tested | **High if it bounces** | Test today |
| 11 | WHOIS `cypadi.com` | Medium | Hosted at Namecheap; registrant unconfirmed | Low | Review registrant details |
| 12 | SPF / DMARC on `cypadi.com` | Low–medium | Unknown | Low–medium | 20 min with your host |
| 13 | RDB 123088034 confirmable | Medium | Not on the site at all | High | Same fix as #4 |
| 14 | Google Business / social footprint | Low–medium | None | Medium | A day, ongoing |
| 15 | Wayback / index history | Low | Nothing archived yet | Low | Host the final version only |
| 16 | Security headers | Low | None present | Low | 15 min |

### If you only fix four things

1. **Host the subdomain, with valid TLS and an `http`→`https` redirect.** Nothing else in this document matters until that works.
2. **Make the GitHub repository private.** It is today the worst exposure you have, and it is the one thing an approver finds by doing the single most obvious search.
3. **Put `Cypadi Ltd · RDB 123088034 · TIN …` in the footer and in Terms §1.** Closes the most likely written finding.
4. **Change the admin credentials and stop uploading `pawapay-documents/` to the web root.** Closes the two probes a technical reviewer runs first.

---

## Launch-day sequence

Run this in order, on the day `shop.cypadi.com` goes live. Each step takes minutes; the sequence is what matters, because a site that is live in the wrong state gets archived and indexed that way.

**Before you upload anything**

- [ ] GitHub repository set to **private**; confirm the URL now returns 404 for a logged-out browser.
- [ ] Admin password and username changed; the published `iqbolshoh` / `IQBOLSHOH` pair fails.
- [ ] Seeded admin account (`Iqbolshoh@gmail.com`, `+998997799333`) deleted or replaced with your own.
- [ ] PawaPay API token rotated if it was ever committed anywhere.
- [ ] Footer carries `Cypadi Ltd · RDB 123088034 · TIN …` and the registered address.
- [ ] Terms §1 and About carry the same legal identity.
- [ ] Refund policy expanded beyond two sentences.
- [ ] `$` replaced with `RWF` in `index.php` (lines 120 and 186).
- [ ] Search form wired to `category.php`, or removed.
- [ ] Four social icons linked to real profiles, or removed.
- [ ] `"Ogani Template"` meta description and keywords line replaced.
- [ ] One phone number chosen for the site; the other either removed or explained on the Contact page.

**Deploy only what the app serves**

- [ ] Upload: `.php` pages, `includes/`, `admin/`, `seller/`, `signup/`, `login/`, `logout/`, `src/`, `assets/`.
- [ ] Do **not** upload: `.env`, `.git`, `.vscode`, `.specstory`, `_tmp_*`, `_`, `pawapay-documents/`, `_preview/`, `*.docx`, `*.md`, `*.sql`, `current_ai-progress.txt`.
- [ ] `.env` placed **outside** the web root, or blocked — and re-tested from a browser.
- [ ] Extended `.htaccess` from Check 5.2 in place.

**Then test, from outside, as the reviewer would**

- [ ] `http://shop.cypadi.com` → redirects to `https://`.
- [ ] Certificate valid, no warning, correct hostname.
- [ ] `https://shop.cypadi.com/.env` → 403 or 404.
- [ ] `https://shop.cypadi.com/.git/config` → 404.
- [ ] `https://shop.cypadi.com/pawapay-documents/` → 404.
- [ ] `https://shop.cypadi.com/database.sql` → 403 or 404.
- [ ] `https://shop.cypadi.com/admin/` → login, and the published pair fails.
- [ ] Every page loads with no PHP warning, notice or stack trace.
- [ ] `info@cypadi.com` receives an external test email.
- [ ] `+250 784 801 809` answered by someone briefed on this application.
- [ ] One real RWF 100 payment from a phone, with the browser closed after the PIN prompt, and the order still reaching `paid`.
- [ ] Google "Cypadi" from a clean browser — confirm the repository no longer appears.

**Only then**

- [ ] Send the submission email.

---

## Appendix — what was actually checked, and when

| Item | Method | Result (11 November 2026) |
|---|---|---|
| `shop.cypadi.com` DNS | `nslookup … 8.8.8.8` | **Non-existent domain** |
| `cypadi.com` DNS | `nslookup … 8.8.8.8` | `66.29.148.173` (Namecheap shared hosting) |
| `www.cypadi.com` | `nslookup … 8.8.8.8` | CNAME → `cypadi.com` |
| Source repository | Fetched `github.com/M-I-Theogene/php-mysql-marketplace` | **Public**, indexed, full file listing visible |
| Repository contents | Directory listing | `pawapay-documents/` (signed PDFs, `sign.png`, `stamp.png`, `_preview/`), all `_tmp_*` files, `current_ai-progress.txt`, `database.sql`, `config.php`, `README.md`, and the three internal review `.md` files |
| `README.md` | Read | Publishes admin `iqbolshoh` / `IQBOLSHOH` |
| `database.sql` | Searched for `iqbolshoh` | Seeded admin row present, with hash and an Uzbek phone/email |
| `config.php` | Read | Password pepper in plaintext; dev database credentials |
| `.gitignore` | Read | Ignores `.env`, `.env.*`, `~$*`, `*.docx`, `.vscode/`, `.specstory/` |
| `.env` tracked in git? | `git ls-files` | **No** — only `.env.example` is tracked; `.env` exists locally |
| `current_ai-progress.txt` | Searched for tokens/passwords | No secrets — task prompts only |
| `.htaccess` | Read | Blocks `.env`, `.htaccess`, `.docx`, `.sql`, `.md`; **does not** block `.git/` contents, `.pdf`, `.png`, `_tmp_*` |
| `index.php` | Searched | Line 8 `"Ogani Template"`, line 9 `keywords: Ogani`, lines 120 & 186 `$<?php echo $price_current; ?>`, line 145 hardcoded `16` |
| `includes/header.php` | Read | Search `<form action="#">` with no handler; four social icons on `href="#"`; header phone `+250 784 801 809` |
| `includes/footer.php` | Read | `Cypadi Ltd · Kigali, Rwanda` — no RDB, no TIN, no street address |
| `terms.php` | Read | 11 sections; names Cypadi Ltd, PawaPay, MTN, Airtel; RWF; refunds to paying number; Rwandan law |
| `privacy-policy.php` | Read | 9 sections; controller, categories, five-year retention, NCSA |
| `refund-policy.php` | Read | Two sentences |
| `about-us.php` | Read | Merchant-of-record; supplier verification; matches the VDD document |
| `contact-us.php` | Read | City, phone, email — no form |
| Phone numbers | Grep of `.md` files and the site | Letter/signature `+250 792 359 800`; every site page `+250 784 801 809` |
| RDB number on the site | Grep for `123088034` | **Not present anywhere on the site** |

*Nothing in this document was inferred: every "state today" entry corresponds to a file read or a command run on the date above. Re-run the tests after hosting — a hosted site is a different surface from a local tree, and several of the exposures listed here only become URL-testable once the subdomain resolves.*
