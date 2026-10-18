# Bank transfer & verification handbook

## Setup by an administrator

AWS Welfare → Organization settings: enter the verified account title, bank name (Meezan Bank), account number and/or valid Pakistani IBAN, optional branch, and an optional approved QR image URL from Media Library. IBAN validation checks Pakistan’s format and mod-97 checksum. Verify every value against the society’s actual bank documents; software validation does not prove ownership.

Required title, bank name, and account number or IBAN must be present. Otherwise the public form is disabled and direct submissions return an error. No account details are bundled. Approved QR codes and public bank details are intentionally public; screenshots submitted by donors are not.

Review the privacy/donation policies and publish them before inviting real donors. Configure HTTPS, private storage, email and cache exclusions. Gateway/recurring options remain hidden because no provider is configured.

## Donor experience

1. Select general fund or an active, published campaign.
2. Choose a preset or custom PKR amount, name/email, optional phone, public-anonymity preference and optional separate marketing consent.
3. Read the verified bank instructions and make the transfer outside the website.
4. Enter the bank transaction reference and optionally attach JPG/PNG/PDF proof (5 MiB maximum, genuine content checked).
5. Consent to processing for the request. Submit for verification.
6. Receive a random `AWS-...` submission reference and a clear explanation that the submission is **not payment confirmation**. It contains no name/email in the URL. The confirmation page is not an unauthenticated donor-data lookup or tax receipt.

A signed, random per-form token plus database uniqueness prevents accidental repeats. Replaying an already stored token returns the same reference. A normalized transaction reference can be recorded only once, including across new forms. An unsuccessful duplicate should be handled by staff, never by inventing a new bank transaction reference. IP-derived rate keys contain HMAC hashes, not raw addresses; public accepted-submission attempts are limited to one per 20 seconds by kind. A honeypot plus WordPress CSRF nonce avoids inaccessible CAPTCHA dependencies. Consider host-level WAF limits for additional abuse control.

## Donation Manager daily workflow

Administrators create staff users with the **Donation Manager** role. That role can review/export donations and download proofs; it cannot edit bank settings, manage users or edit public content. Standard Content Editors can edit campaigns/pages but cannot read private donation records, proofs, settings or enquiry records. Administrators control everything.

1. Open AWS Welfare → Donations. Filter by status, campaign ID and local date boundaries. Dates display in Asia/Karachi; UTC is stored internally. The list is paginated.
2. Open a pending reference. Match account statement, exact amount, transaction reference and purpose. Do not approve solely because a screenshot looks plausible.
3. Choose Confirm or Reject, add a reason, and check the explicit financial-change confirmation box.
4. Approval changes status and appends actor, timestamp, old/new statuses and reason in one InnoDB transaction. Repeated approval does not double-count or duplicate the verification event.
5. Confirmation attempts a bilingual donation acknowledgment via wp_mail. The email is **not a tax receipt**. “Accepted” means accepted by the configured mail transport, not proven inbox delivery. Failed mail is visible; the financial confirmation is not rolled back. Use Send/retry acknowledgment after fixing delivery.
6. Public totals include confirmed, non-refunded donations only. No donor names or public donor list are exposed, regardless of anonymity choice. The anonymity field preserves intent for any future reviewed recognition feature. Internal staff still see necessary details.
7. If a confirmed donation is refunded outside the site, choose Record completed refund, provide the reason and explicitly confirm. This removes its amount from net confirmed totals. **The site does not send money or initiate bank refunds.**

Allowed transitions are `pending → confirmed`, `pending → rejected`, `confirmed → refunded`. Rejected/refunded states cannot silently be reopened. No UI edits donor financial amounts or deletes financial history. Staff should not put passwords, bank screenshots, unnecessary personal information or secrets in audit reasons. Disputed/corrected records require an organization-approved process rather than hiding a prior verification decision.

## Private proof review

Only authenticated authorized donation staff can use the nonce-protected download action. Files are outside the public web root, named randomly, served as attachments with no-cache and a sandbox header, and absent from the Media Library. Never move them into public uploads, publish download URLs, or forward screenshots casually. If private storage is absent/misconfigured the upload control is unavailable; reference-only submissions still function.

## CSV exports

Donation exports include private operational data and paisa integer amounts. They require the export capability and a nonce. Formula-like cells, including leading whitespace before `=`, `+`, `-`, `@`, tab/newline, are prefixed to prevent spreadsheet formula execution. Treat downloaded CSVs as confidential, store securely and delete under the approved retention policy. Date filtering applies to the admin list; the labeled export exports **all records**, with UTC dates, for controlled reconciliation.

## Totals vs delivery

Fundraising is a database aggregate of confirmed contributions. Delivery is staff-entered dated milestones and updates. Collecting the target does not establish procurement, distribution or project completion. Approved metrics require definition, period, evidence and an authorized editor. No “100% donation,” tax deductibility or registration promises are included.

## Version 1.1: steps and private status access

The donation form enhances to three in-page steps (contribution, donor details, transfer) and a fourth confirmation screen. Back/Continue preserve values in the page; no personal data is put in browser storage. Without JavaScript all fieldsets remain visible and usable. The homepage amount-entry panel carries only a non-sensitive amount to Donate. Language switching warns before discarding unsent information.

A successfully stored donation grants a random 256-bit private access token. Only its SHA-256 hash and expiry are stored in the database. Confirmation shows the submitted amount, campaign/general fund, reference and current pending-verification state through this verified access. The private status page displays state, reference, amount, purpose and Karachi submission date, **never donor names, email or proofs**. A reference alone cannot retrieve the record.

A secure link is also requested by email. `/donation-status/` can reissue a link only to the matching stored donation email/reference; public responses are generic and rate-limited. Links expire after 30 days. Reissuing or sending the confirmed acknowledgment rotates the token and revokes older links. Email failure does not undo a donation; the initial verified confirmation access works, and staff must resolve actual email delivery. Never put access tokens into analytics/logging exports. The pages are no-store/noindex with no-referrer responses; enforce the same exclusions at your CDN.

After confirmation the private status page offers a UTF-8 plain-text **donation acknowledgment**, not a tax receipt. It requires valid unexpired access and a confirmed record. Pending/rejected/refunded or invalid/expired access cannot download it. Privacy erasure revokes access tokens as well as removing proofs/contact identity. This site does not expose a publicly searchable donor directory.
