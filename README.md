# AWS Welfare

The installable bilingual WordPress implementation is in [wordpress/](wordpress/README.md), including separate theme/plugin ZIPs, source, tests and operations guides. It provides persistent manual bank-transfer verification and administration. WordPress needs PHP/database hosting; GitHub Pages cannot run it.

## GitHub Pages client preview

The repository root now contains a static export of the actual WordPress theme for client review at https://shayanahmed226.github.io/AWS-Welfare/. It includes 66 English/Urdu pages, including clearly labeled editorial examples and review-draft policies. The WordPress packages are version 1.1 with private donation status, application workflows, blog, reports and gallery. Payments and form submissions are deliberately disabled; WordPress administration and persistent workflows still require PHP/database hosting.

Regenerate from a local WordPress install with `node wordpress/tools/export-preview.cjs`. The default output is `/tmp/awsw-client-preview`; publish that directory to GitHub Pages. The exporter preserves the theme’s local fonts and assets.

## Earlier static prototype


Responsive, animated website for Armughan Memorial Welfare Society, built with Vite and vanilla JavaScript.

## Develop

Requires Node.js 20.19+ or 22.12+.

```sh
npm ci --cache /tmp/aws-npm-cache
npm run dev -- --port 5173
```

## Production

```sh
npm run build
npm run preview
```

The commands below belong to the legacy Vite prototype. Running its build overwrites the repository-root client preview; regenerate the WordPress preview before deployment. Deploy the generated `dist/` folder to a static host. The build also writes production files to the repository root, so GitHub Pages can serve either `main` / (root) or `gh-pages` / (root). Source files live in `site/`; run `npm run build` before publishing changes.

## Integration notes

- Donation interactions are explicitly labeled previews. No payment is collected.
- The volunteer form downloads a local JSON interest note. No information is transmitted or stored by the society.
- Replace `site/public/emblem.svg`, a temporary brand-inspired emblem, with the supplied official logo asset when available in the repository.
- Images use Unsplash and fonts use Google Fonts; these require internet access in visitors’ browsers.
- No unverified impact numbers, payment credentials, or contact details are included.
- Animation respects the system reduced-motion setting.
