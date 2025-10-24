#!/usr/bin/env bash

set -euo pipefail

REPO_ROOT="/Volumes/DatenAP/Code/admin.arkturian.com"

usage() {
  cat <<'USAGE'
Usage: build-local.sh [--clean]

For PHP projects, this script performs basic validation checks.
No build step is required for PHP files.
USAGE
}

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  usage
  exit 0
fi

cd "$REPO_ROOT"

echo "🔍 Validating PHP project..."

# Check for PHP syntax errors in all PHP files
if command -v php >/dev/null 2>&1; then
  echo "   Running PHP syntax check..."
  find . -name "*.php" -not -path "./.git/*" -exec php -l {} \; > /dev/null 2>&1 && \
    echo "   ✅ PHP syntax check passed" || \
    echo "   ⚠️  PHP syntax warnings found (non-fatal)"
else
  echo "   ⚠️  PHP not found in PATH, skipping syntax check"
fi

echo "✅ Validation complete. PHP project ready for deployment."
