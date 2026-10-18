# Installation

## Hosting requirements

A PHP/database WordPress host is required for `https://awswelfareorg.com/`. GitHub Pages only serves the separate static prototype. DNS/hosting credentials and authorization to replace an existing live site have not been supplied; no production domain or existing site was changed.

Use HTTPS and an actively supported WordPress release, PHP 8.1+ with mysqli, fileinfo, mbstring and image support, MySQL 8+ or MariaDB 10.6+, InnoDB tables, persistent disk, backups, and an email transport. The implementation uses native WordPress functionality; no paid licenses. WordPress core/plugin security updates remain a host responsibility.

## Install the two ZIPs

1. Back up any existing site and test on staging first.
2. WordPress → Plugins → Add New → Upload Plugin → `aws-welfare-core.zip` → Activate.
3. Appearance → Themes → Add New → Upload Theme → `aws-welfare-theme.zip` → Activate.
4. AWS Welfare → Organization settings → **Create missing starter pages**. This is explicit and idempotent: existing pages are never replaced. Home becomes the front page only if none was configured. Timezone is set to Asia/Karachi. Policies are created as drafts. Until the privacy page is published, forms show a readable, clearly labeled inline privacy draft rather than a broken policy link. The bank panel warns against real transfers until the donation policy is approved.
5. Settings → Permalinks → Post name → Save. Apache/Nginx must route pretty permalinks to WordPress.
6. Appearance → Customize: upload official logo, set Site Identity and brand colors. Without a supplied file a temporary text wordmark is used. English Poppins and Urdu Noto Sans Arabic fonts are self-hosted under their included SIL OFL licenses.
7. Appearance → Menus: optional separate `Primary navigation (English)` and `(Urdu)` menus. Automatic bilingual navigation works before menus are assigned. On subdirectory installs use the menu picker instead of hardcoded root paths.
8. Enter verified bank and organization details under AWS Welfare → Organization settings. Never use local test data in production. See DONATIONS.md.
9. Create campaigns, approved project updates, reports and verified metrics. See CONTENT.md.
10. Review and edit the policy drafts, publish both languages, and check the policy approval checkbox only after organization approval. Set the Privacy Policy under Settings → Privacy.

## Private proof storage

Proofs must not be WordPress Media Library uploads. Provision a persistent private directory **outside the web document root** and readable/writable only by PHP. Example host-level arrangement:

```
/var/www/html/             # public WordPress
/var/www/awsw-private/     # private, persistent, owned by PHP user, mode 0700
```

In `wp-config.php`, before the stop-editing comment:

```php
define('AWSW_PRIVATE_DIR', '/var/www/awsw-private');
define('DISALLOW_FILE_EDIT', true);
```

Never paste credentials in theme/plugin files. The directory must already exist, resolve outside both WordPress and the server document root, and be writable by PHP. If it fails validation, proof upload is hidden and server-side proof submission rejects files. Reference-only transfers still work. Set `upload_max_filesize = 6M` and `post_max_size = 8M` or higher; the plugin still enforces a 5 MiB maximum. Accepted types: genuine JPEG, PNG, PDF. Back up this directory separately and restore its permissions.

## Local development with Docker

1. In `wordpress/`, copy `.env.example` to `.env`; enter two different strong random local database passwords. Do not commit them.
2. `docker compose up -d` (requires Docker and access to verified Docker Hub images).
3. `docker compose exec -u root wordpress sh -c 'chown www-data:www-data /var/www/awsw-private && chmod 700 /var/www/awsw-private'`.
4. Complete the normal WordPress installer on the local mapped port 8088. Upload the ZIPs as above. Do not expose this local stack as production.
5. Keep volumes. `docker compose down` preserves them; `down -v` destroys local data and requires deliberate backup first.

For source refresh on your own local stack, copy source folders into wp-content with readable permissions or reinstall freshly packaged ZIPs. Do not modify core WordPress files. Each Codex task already has an isolated workspace: use the existing checkout, and do not create a worktree unless explicitly requested.

## Cache rules

Exclude `/donate/`, `/donation-confirmation/`, `/contact/`, `/volunteer/`, all `wp-admin/admin-post.php` requests, authenticated administration and any private responses from page/CDN caching. Donation forms contain nonces and unique submission tokens. Respect `lang=en|ur` in public cache keys. Do not log POST bodies or include donor data in analytics. Public aggregate pages may be cached, but purge campaign caches after verification/refund or use short TTLs; the `awsw_donation_status_changed` hook is available for your host’s cache purge integration.

## Fresh installation validation

Both ZIPs were installed and activated via WP-CLI against a clean official WordPress Docker image, then exercised through Apache and Chromium. Full findings and limits are in TEST-REPORT.md. This does not establish installation or email delivery on your eventual production host.
