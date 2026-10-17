import { cpSync, mkdirSync, writeFileSync } from 'node:fs';
// Keep the root directly deployable by GitHub Pages; source lives in site/.
// Retain older hashed assets so already-open pages can finish loading.
mkdirSync('assets', { recursive: true });
cpSync('dist/assets', 'assets', { recursive: true });
cpSync('dist/index.html', 'index.html');
cpSync('dist/emblem.svg', 'emblem.svg');
writeFileSync('.nojekyll', '');
writeFileSync('dist/.nojekyll', '');
