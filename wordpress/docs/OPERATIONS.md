# Security, privacy, backups and operations

## Storage and permissions

Campaigns/updates/reports/metrics are WordPress custom post types. Core operational data lives in `{prefix}awsw_donations`, `{prefix}awsw_audit` and `{prefix}awsw_enquiries`; application reviews are recorded in `{prefix}awsw_case_events`. Tables are InnoDB; monetary values are unsigned integer paisa. All operational queries use WordPress database APIs and prepared variable parameters. Staff transitions are transactional and append-only. Changing themes/deactivating the plugin never removes this data; the plugin intentionally has no uninstall deletion routine.

Proof files are in the host-provisioned `AWSW_PRIVATE_DIR`, outside public roots, with private directory permissions and file mode 0600. The plugin rejects wrong extensions/content, executable/SVG files and oversized files. Public financial proof URLs do not exist. Review PHP settings, web root and permissions on your actual host. An OS/host compromise is outside the protection offered by WordPress capabilities.

Use individual staff accounts, strong unique credentials and host-supported MFA. Administrators have full access; Donation Managers have only read/review/export capabilities; Content Editors have public-content permissions and cannot access private donor data or bank settings. Limit administrator accounts. Do not install unreviewed plugins with broad administrator powers.

## Retention and verified data requests

The organization must approve specific retention periods and legal obligations before launch; no arbitrary legal compliance or retention period is claimed. An optional settings field records the reviewed enquiry/unconfirmed-record retention period as an operational reminder. There is **no automatic deletion job for financial records**.

Enquiries may be permanently deleted individually by authorized administrators with a nonce and explicit checkbox. Retention reviews should be scheduled by staff. Before deletion verify whether the enquiry must be retained under organizational policy. Financial status records and audit history are never silently deleted.

WordPress Tools → Export Personal Data / Erase Personal Data integrates the plugin under confirmed subject email requests. Exports include matching donor records and enquiries. Erasure removes donor contact fields, public-recognition/marketing preferences, transfer reference text and private proof files; it preserves financial amount/status, random submission reference, pseudonymous transfer fingerprint and verification history. Audit notes must be reviewed separately for accidental personal information; do not claim a data request is fully completed without that review. Erasure is not a refund. Staff must decide any required retention/exceptions before initiating WordPress’s confirmed-request workflow.

Rate limiting stores HMAC-derived IP keys with a 20-second active window. A daily scheduled cleanup removes expired keys in batches of up to 1,000; monitor WP-Cron for execution and run extra cleanup during heavy abuse. Server access logs may contain IP addresses independently of plugin data; configure host log retention and access restrictions. Avoid logging POST bodies, SMTP secrets and bank proofs. No analytics integration or donor-data event tracking is included.

## Backup

Back up all of:

1. Full database (including custom tables, postmeta, options and users).
2. `wp-content/uploads` for approved public images/reports.
3. The entire private proof directory, encrypted and access-controlled.
4. Theme/plugin packages, version/checksums, and a protected copy of `wp-config.php`/host secret references.
5. Host web routing, mail and cache configuration.

Take consistent database and private-file snapshots while writes are paused or with an approved consistent-backup mechanism. Store encrypted backups off-host with limited access and an organization-approved schedule. Do not place database dumps or archives under the public document root or commit them to Git. Test restoration periodically.

## Restore procedure

On an isolated staging environment provision matching PHP/database services. Restore the database and approved public uploads. Restore private proofs to a nonpublic directory owned by the PHP user, set `AWSW_PRIVATE_DIR`, and verify no public access. Install the archived theme/plugin versions. Restore secrets via the hosting provider; never into tracked files. If the URL changes use WP-CLI search-replace with a dry-run first and preserve serialized data. Flush permalinks, disable real outbound email during staging, and recheck campaign totals, staff permissions, proof downloads, both languages and a synthetic transaction before production switch-over.

Backup/restore instructions are supplied; restoration of a real organization's backup and disaster-recovery timing have not been tested because no production dataset/host is supplied.

## Deploy/update

Build/test on staging and back up first. Package with `tools/package.sh`. Verify `sha256sum -c SHA256SUMS`. Upload matching theme/plugin ZIPs; WordPress can replace the existing versions after review. Schema version is checked on activation/init, with non-destructive dbDelta changes. Large future migrations require a separate tested plan. Purge host caches for public aggregates after status changes, without caching private routes. Test before repointing DNS or replacing a live site. Keep WordPress, PHP and database supported and patched.

Use the existing isolated checkout for development; do not create a Git worktree unless explicitly requested. Processes need starting again in a fresh task. Files/volumes alone are not evidence that WordPress/database processes are running.

Private access hashes/expiry in donation records must remain confidential. Exclude donation-status and submission-confirmation responses from all page caches. Assistance records require their separate capability; do not give that capability to ordinary Content Editors. Case erasure/deletion also removes related private review notes, while financial history remains append-only.

Uploaded proofs are untrusted documents even after extension/content validation. Review them with maintained software and the host's approved scanning/review process; never move them into public media. Public gallery consent is a separate workflow from financial proof review.
