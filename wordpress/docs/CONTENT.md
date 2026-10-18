# Campaigns, Gutenberg and bilingual editing

## Public pages

The starter-page action creates Home, About, Programs, four program detail pages, Campaigns, Donate, Impact & Updates, Transparency, Volunteer, Contact, Donation submission, Payment not completed, and draft Privacy/Donation policy/Terms pages.

The homepage is **native Gutenberg blocks**, including editable headings, paragraphs, groups, buttons and details/FAQ blocks. Campaign/metric/update lists are shortcodes so database changes appear dynamically. About and policy starter copy is saved as editable blocks. Other directory/form pages combine editable blocks with operational shortcodes. You can insert the theme’s Community call to action and Program introduction patterns in the editor. Dynamic program cards and labels have centralized English/Urdu starter copy in the plugin; use surrounding Gutenberg content for organization-specific narrative.

`[awsw_campaigns]`, `[awsw_donate]`, `[awsw_confirmation]`, `[awsw_programs]`, `[awsw_program program="water"]`, `[awsw_featured]`, `[awsw_metrics]`, `[awsw_updates]`, `[awsw_updates type="awsw_report"]`, `[awsw_enquiry type="volunteer"]`, `[awsw_enquiry type="contact"]` provide the core functionality. Forms work without JavaScript; JavaScript adds preset buttons, clipboard copy and mobile navigation enhancements.

## Translation approach — no paid dependency

A single WordPress record stores the English title/content and paired Urdu title/content in protected `_awsw_ur_*` post metadata. The language switch changes `?lang=en` / `?lang=ur` on the **same page**, including campaign details. No unrelated page fallback. Missing campaign/ordinary page translations display an explicit notice and the original English, rather than pretending to be translated. Add Urdu for every live record before launch.

Edit English in Gutenberg. In the **AWS Welfare · Translations & verified fields** box, enter Urdu title and safe HTML or Gutenberg block markup in Urdu content. The frontend uses `lang=ur-PK`, `dir=rtl`, local Urdu fonts and appropriate spacing. IBANs, email/telephone fields and references remain LTR. Form messages, buttons, confirmations and donation emails have native Urdu copy. Staff admin labels remain English. WordPress core admin translation packs may be installed separately if needed; no automatic machine translation service is called.

For a visually authored Urdu page: compose blocks in a temporary draft, switch to Gutenberg Code editor, copy its block markup to the original record’s Urdu content field, then discard the temporary draft. Review Urdu naturally with the organization; starter Urdu is not a substitute for organization approval. Admin menu locations allow separately editable navigation for each language.

## Campaigns

1. Campaigns → Add New. Add a truthful English title, description, approved cover image and reviewed use of funds.
2. Select Food & ration, Education, Clean water or Medical support under Program.
3. Set the PKR target using decimals (stored as integer paisa), general location, optional dates and status. Status is explicit: draft, active, paused or completed. Dates are informative; staff must pause/close the campaign when appropriate.
4. Enter Urdu title, description, location, use of funds and delivery milestones. Without approved figures leave targets blank; do not create pretend numerical claims.
5. Publish only after review. Only published campaigns explicitly marked `active` appear as donation choices. General fund remains available when bank details are ready.
6. Add delivery milestones one line each: `YYYY-MM-DD | completed action`. Supply the corresponding Urdu entries. Milestones are staff reports, never inferred from money raised. Add supporting dated Project Updates and approved galleries in Gutenberg.
7. Public totals query confirmed records, exclude pending/rejected/refunded amounts, display actual totals beyond target, and cap only the graphical progress bar. Zero/missing targets have no percentage or division error. Target changes do not change donation amounts or history.

## Updates, reports and metrics

Project updates have publish dates, English/Urdu content, program tags and optional related campaign ID. Use Gutenberg galleries only for approved photographs. PDFs uploaded to Media Library are public: only approved transparency reports/registration documents belong there, **never donor proofs**. Reports have period, related campaign ID, English/Urdu copy and approved PDF URL. Publication indicates editorial approval.

Verified metrics require a defined title, value, reporting period, internal evidence reference and a recorded authorized editor. Incomplete/unverifiable metrics are excluded from the homepage/impact display. Evidence references are private post metadata and not printed publicly. The save handler records the authorized editing user. Publish only reviewed data; editing permissions are not evidence that a number is true. Explain definitions and periods in the metric’s public content.

SEO descriptions are editable in the same meta box. Titles use the WordPress document title; Urdu gets its corresponding title. Canonicals and English/Urdu hreflang are generated. WordPress core supplies XML sitemaps for published public content. Only verified organization name/site URL appear in Organization structured data. If adding an SEO plugin, disable overlapping canonical/hreflang/OG output to avoid duplicates.

## Optional local demo

Only with `WP_ENVIRONMENT_TYPE=local` and WP-CLI:

```
wp eval-file /path/to/wordpress/tools/demo.php import
wp eval-file /path/to/wordpress/tools/demo.php remove
```

The importer creates one visibly labeled, paused, local-only sample campaign with a demo target. It creates no bank details, donations, beneficiaries, metrics or historical claims. Re-import is idempotent. Removal deletes only tagged demo records; a demo campaign with financial records is unpublished instead of deleting its financial association. Never import demos on production.
