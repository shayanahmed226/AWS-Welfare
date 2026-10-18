#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
mkdir -p releases
find aws-welfare-theme aws-welfare-core -type d -exec chmod 755 {} +
find aws-welfare-theme aws-welfare-core -type f -exec chmod 644 {} +
# Always recreate archives to prevent removed source files surviving in older ZIPs.
rm -f releases/aws-welfare-theme.zip releases/aws-welfare-core.zip releases/aws-welfare-source.zip
zip -qr releases/aws-welfare-theme.zip aws-welfare-theme
zip -qr releases/aws-welfare-core.zip aws-welfare-core
zip -qr releases/aws-welfare-source.zip aws-welfare-theme aws-welfare-core docs tests tools README.md LICENSE.txt compose.yaml .env.example
(cd releases && sha256sum aws-welfare-theme.zip aws-welfare-core.zip aws-welfare-source.zip > SHA256SUMS)
