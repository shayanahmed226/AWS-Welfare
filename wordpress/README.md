# AWS Welfare — WordPress implementation

This is the installable WordPress product. The repository-root GitHub Pages site is a static client preview exported from this theme and cannot process donations. The earlier Vite prototype remains under site/. GitHub Pages cannot run these PHP packages.

## Packages

- `releases/aws-welfare-theme.zip`: custom theme; presentation, responsive/RTL layouts, native Gutenberg patterns, local licensed fonts, editable palette and logo.
- `releases/aws-welfare-core.zip`: independent plugin; campaigns, bank transfers, private proofs, verification/audit, enquiries, bilingual rendering and privacy tools.
- `releases/aws-welfare-source.zip`: complete WordPress source, operational documentation, test scripts and optional local demo tool.
- `releases/SHA256SUMS`: archive integrity checks.

No paid license, page builder, translation subscription, gateway plugin or external font service is required. Use an actively supported WordPress release (minimum 6.6), PHP 8.1+ (tested 8.3), MySQL 8/MariaDB 10.6+ (tested 11.4), fileinfo and mbstring, HTTPS, and a configured email transport. Use InnoDB for financial transactions.

## Start here

1. Follow [installation](docs/SETUP.md).
2. Configure verified details and review [launch blockers](docs/LAUNCH.md).
3. Read [campaigns and translations](docs/CONTENT.md) and [bank-transfer verification](docs/DONATIONS.md).
4. Configure [email and gateway prerequisites](docs/INTEGRATIONS.md).
5. Arrange [private storage, retention, backups and restore](docs/OPERATIONS.md).
6. Review the [executed test report](docs/TEST-REPORT.md).

No bank account, official contact, registration claim, beneficiary photograph, testimonial, impact count or live campaign is bundled. Payment submissions are disabled until required bank details are configured. Policies begin as review drafts. No online gateway is enabled.

## Source map

`aws-welfare-core/includes/`: data model, public handlers, operational/admin workflows, bilingual helpers, editable starter content.

`aws-welfare-theme/`: theme templates, Gutenberg patterns, theme.json, CSS/JavaScript and SIL OFL-licensed font files.

`tools/demo.php`: explicit local-only sample campaign importer/remover. It creates no donations or bank details.

`tests/`: financial, real-browser and security tests requiring a disposable installation. They are not run on activation and must never run against production.

Rebuild archives with `bash wordpress/tools/package.sh` from the repository root. Activate the plugin independently of the theme; deactivating or changing the theme never deletes operational data. There is intentionally no automatic uninstall deletion.

## Client preview on GitHub Pages

`node wordpress/tools/export-preview.cjs` exports 26 English/Urdu pages from the clean local WordPress installation (port 8089 by default) to `/tmp/awsw-client-preview`. It preserves the actual theme rendering, self-hosted fonts and navigation, maps paths for `/AWS-Welfare/`, labels the site as a client design preview, and disables submissions without exposing WordPress nonces or personal-data form names. Set `AWSW_PREVIEW_SOURCE` and `AWSW_PREVIEW_OUT` to override the local source/output. Publish the export directory to GitHub Pages; the live admin/database are not included.
