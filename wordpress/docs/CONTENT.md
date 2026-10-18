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

## Version 1.1: expanded platform pages

New starter routes: `/team/`, `/get-involved/`, `/volunteer-application/`, `/request-assistance/`, `/partnerships/`, `/in-kind/`, `/blog/`, `/updates/`, `/gallery/`, `/faq/`, `/donation-status/`, `/submission-confirmation/`.

Volunteer is now an overview with an application CTA; applications have a dedicated page. Impact and Transparency use the new period/type/year-aware directories. On an existing site the starter creator **preserves authored pages**: review existing Volunteer, Impact and Transparency content and replace the old starter shortcodes with `[awsw_volunteer_overview]`, `[awsw_impact_extended]`, and `[awsw_transparency][awsw_reports]` respectively. Update Home with the new starter layout only after backing up your authored content. No migration silently overwrites your page copy.

New sites receive editable homepage blocks (including quick contribution entry and latest articles), editable static overview/participation narrative, and native HTML blocks for program introductions plus live dynamic campaign/update sections. Gutenberg HTML blocks remain editable and can be replaced with native heading/paragraph/image blocks. Dynamic interface labels/shortcodes are bilingual PHP components; copy around them is authored in WordPress. Keep operational form shortcodes intact.

### Editorial content

Blog uses native WordPress posts/categories and real author/publication dates, with search, category filtering, pagination, featured layout, related articles and clipboard sharing. The default WordPress “Hello world” sample is excluded. Draft example articles can be authored explicitly; none are published as fictitious organizational activity. Add Urdu excerpt/content/title in the AWS meta box. Featured images and optional credit must be approved. Long-form headings, lists, quotations, embeds and galleries use Gutenberg. No automatic table of contents dependency is introduced.

Project Updates have search and program filtering, paginated results, location, milestone status, related report, dates and bilingual content. Report directories filter by **publication year** and Report Type (Financial, Project, Registration), paginate, and link approved public PDFs. Reporting periods remain separate editable fields; a PDF is not created merely by publishing a report post.

Impact’s reporting-period selector appears only when periods exist in published metrics. Values still require evidence/authorized editors; unsupported charts are not rendered. Completed project highlights use explicitly completed campaigns, never a funding-percentage inference. Blog, gallery, reports and metrics retain honest empty states.

### Team, gallery and privacy

Use Team Members for real approved profiles, role/responsibility text and photographs. No seeded people/advisers. The public Team page links governance and transparency. Fill actual story dates in About’s editable timeline section; no dates are invented.

Use Approved Gallery to create photo records. **Upload only photographs already approved for public use**, set a featured image, caption/title, program and Urdu fields, and enter `1` in the gallery consent/approval field only after review. The directory excludes other records. Attachment files in WordPress Media Library are public; review image consent **before uploading**, not only before publishing a gallery record. Gallery viewer supports keyboard open, previous/next arrows, Escape and restored focus; without JavaScript the original image link remains usable. Never use this gallery for donor proofs or assistance documents.

### Reusable UI and motion

Tokens live in theme CSS (`--awsw-space-*`, radius/shadow/motion/content and brand colors) plus theme.json. Interface icons use one inline SVG family. No Tailwind CDN, Laravel backend, animation library or paid dependency. Entrance transitions are 180–300ms; content is never initially hidden. Continuous decorative rotation was removed. Reduced-motion overrides disable transitions/animation. Grouped navigation uses native accessible details/summary dropdowns and Escape handling; it also works without JavaScript.

Optional editorial-only samples: `wp eval-file tools/editorial-demo.php import` creates three clearly labeled **draft** educational articles on a local environment, without fictional project history or figures. `remove` deletes only those tagged samples. They are never published by plugin activation. The GitHub design preview may include explicitly labeled examples for visual review; they are not organizational news. Review/strip GPS and other sensitive metadata and check consent before uploading any approved public photographs.
