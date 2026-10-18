# Executed QA report

Date: 18 October 2026 (Asia/Karachi).

Test environment: WordPress 7.1.3, PHP 8.3.35, disposable local official WordPress/PHP Apache Docker image, MariaDB 11.4, real headless Chromium via Playwright. All bank/donor fixtures were explicitly synthetic local QA data; no real bank transfer was attempted. The production domain/hosting was not modified.

## Executed and passed

- Installable plugin and theme ZIPs installed and activated on a clean WordPress instance using WP-CLI; starter pages generated and served by Apache.
- Second clean-install browser verification: **18 bilingual routes** passed at 390px with no overflow; anonymous proof access denied; repeated starter generation preserved all pages and all three organization policies remained drafts. Default WordPress’s separate privacy draft was preserved. Clean install contained no bank details or donations.
- All delivered PHP files passed `php -l`.
- Financial suite: **32 assertions passed**. Missing-bank disablement; integer paisa parsing and invalid inputs; CSV formula escaping; outside-root private storage; database insertion; pending exclusion; approval included once; repeat approval idempotence with a single audit event; overfunding/actual amounts; zero-target and target changes; rejection/refund exclusions; terminal-state protection; content-editor denial; donation-manager least privilege; campaign/donation persistence across theme switching.
- Public browser suite: **47 assertions passed**. English/Urdu homes at 360/390/768/1440px, correct RTL/LTR and no horizontal overflow; corresponding-page language switch; mobile navigation; all 12 tested Urdu public page routes; campaign search/program/status filters; server-side invalid-amount and CSRF denial; valid pending donation persistence and confirmation redirect; identical-token replay and duplicate-bank-reference protection; unauthenticated proof denial; saved volunteer and contact requests; essential Urdu navigation and donation form without JavaScript; helpful 404 and search results; no JavaScript runtime errors.
- Security/browser suite: **19 assertions passed**. PHP disguised as JPG, SVG and oversize proof rejection; genuine PDF private storage; random filenames; no public upload URL; actual HTTP role denial for editor settings/data/proofs and manager settings; authorized proof download as an attachment with no-cache; copied proof URL denied anonymously; staff approval form persists status and displays audit reason; Urdu donation no overflow at 360px; complete keyboard-only donation submission; campaign public REST remains available without a donor-record REST API.
- Automated axe-core checks using WCAG 2 A/AA, 2.1 AA and 2.2 AA rule tags: **zero reported violations** on English/Urdu home, English/Urdu configured donation form, and English volunteer form at 390px. This is an automated subset, not a WCAG conformance certification.
- English/Urdu screenshots inspected, including 1440px desktop and 390px Urdu mobile.
- Extended suite: **12 assertions passed** for linked-campaign deletion protection, sanitized/oversized audit reasons, rate limiting, IBAN validation, enquiry persistence, privacy export/erasure, private-proof deletion with financial-history preservation, and exclusion of metrics without evidence.
- Optional local demo import, idempotent re-import and removal passed on a second clean installation.
- Final policy-draft regression check: English/Urdu inline privacy notices were readable without linking to unpublished policy pages.
- Self-hosted fonts and bundled licenses checked. No external photo/font dependency is required by the WordPress theme.

## Mail behavior

Financial tests forced `wp_mail` failure and verified that the donation stays confirmed with `email_state=failed`. Real admin notification/acknowledgment delivery is not verified; a domain-authenticated mail service and test inboxes are still needed. A returned `accepted` state means mail transport handoff, not inbox delivery.

## Not executed / launch limitations

- Real Meezan settlement, account ownership, real refunds and merchant gateway callbacks: no real bank details/provider supplied. Manual workflow was tested with local synthetic references only.
- Production installation, DNS/HTTPS switch, CDN caching and private-storage validation on awswelfareorg.com: no hosting access supplied.
- Production backup restore/disaster recovery or recovery-time guarantee: no actual production backup supplied.
- Human screen-reader audit, assistive-device matrix, organization-approved Urdu/legal review, load/concurrency tests, penetration test or WCAG certification: not completed.
- Live email deliverability, provider uptime and recipient inbox/spam placement: not configured.
- A specific Lighthouse/performance score: not measured or promised.

## Reproduction

Use a **disposable local** WordPress setup only. The tests create synthetic records/users and exercise staff financial changes. In the onboarding QA stack the container names are `awsw-wp` and `awsw-db`; local credentials live only in `/tmp/awsw-test.env`, never in these ZIPs. The browser/security scripts use these named local containers and controlled fake inboxes; adapt them to your isolated test runner, not a production host.

1. Install both packages and create pages. Set `WP_ENVIRONMENT_TYPE=local`, provision outside-root private storage.
2. Before the financial suite, leave bank settings empty. `wp eval-file tests/financial.php` in the WordPress runtime.
3. Run `node tests/browser.cjs` then `node tests/security.cjs` with Playwright/Chromium available, targeting the documented local QA stack. Browser tests add explicitly synthetic bank settings; remove them after QA. Security tests use random local credentials from the ephemeral env file.
4. Optional: install `@axe-core/playwright` in an isolated QA tools directory and set `AWSW_AXE_MODULE` to it, then run `node tests/accessibility.cjs`.
5. See `tests/` source for assertions. Do not seed QA data into production or interpret synthetic campaign values as organizational activity.

## Version 1.1 extension validation — 18 October 2026

The expanded theme/plugin were installed on a new empty WordPress instance (separate database prefix) using the real installable ZIPs. The prior operational QA install was also upgraded non-destructively. The legacy financial suite passed **32 assertions**, updated original public-browser suite **47**, and private-proof/security suite **19** on the expanded implementation.

New suites executed:

- **85 platform assertions**: new English/Urdu routes; all five requested home widths; dropdown keyboard/Escape behavior; amount handoff; donation step persistence; language-reset warning; real stored submission and pending summary; private 256-bit access, reference-only denial, invalid/expired/revoked links and confirmed acknowledgment download; all four case kinds stored once on replay and initially new; recoverable server-error value preservation/retry; FAQ filtering; no-JS donation/navigation; no JS runtime errors.
- **16 editorial/admin assertions**: real WordPress posts/search/pagination/single article/clipboard; searchable project updates; report year/type filters; evidence-backed metric-period selection; approved-only gallery, keyboard viewer/arrow/Escape/focus restoration; separate assistance permission, query-parameter bypass denial, authorized application review/audit, and bank-setting denial for application staff.
- **240 responsive route checks**: 24 public routes × English/Urdu × 360/390/768/1024/1440px on a fresh ZIP installation, with HTTP success, a single H1 and no horizontal overflow.
- Automated axe-core tagged checks on English/Urdu home and configured donation flow plus English volunteer: **zero reported violations** after correcting small step-number contrast. These remain automated checks, not WCAG certification.
- Anonymous direct REST access to internal gallery/team records is denied; only approved public gallery rendering is intended for visitors. Public Media Library files must be approved before upload.

Regression findings fixed during implementation: legacy form `name="action"` shadowing the JavaScript form action property; Urdu recursive excerpt translation when campaign cards render inside the homepage; late admin permission denial returning an already-sent 200 response; and low-contrast small step labels. Required workflows were rerun after corrections. No production transfer, real email delivery, supplied-organization content validation, real-host backup restoration or gateway was tested.

Version 1.1 test sources: `platform-v11.cjs`, `editorial-fixtures.php`, `editorial-v11.cjs`, `responsive-v11.cjs` plus the updated original suites. The QA editorial fixture creates **clearly labeled local synthetic test content** only; it is not bundled as live organization data and must not be copied to production. Screenshots were reviewed separately. Third-party editorial records/proofs remain controlled by WordPress/staff permissions.

Final client-export checks covered **40 English/Urdu static routes**, including review-draft policies and labeled educational demo articles, local asset/font loading, a single interactive design-only donation wizard and disabled payment/data submission. That test caught and corrected a mobile footer layout override when policy links were present. The client export uses server-rendered markup without pre-running enhancement scripts, avoiding duplicated step controls. The exported site contains no real bank account or submitted records.
