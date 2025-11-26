#!/usr/bin/env bash

set -euo pipefail

# Resolve repository root relative to this script so it works everywhere.
SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
REPO_ROOT="$(cd -- "$SCRIPT_DIR/../.." && pwd -P)"

usage() {
  cat <<'USAGE'
Usage: build-local.sh [--clean]

Validates the repository before release. For Node.js projects, runs npm build.
For PHP projects, performs syntax validation.
USAGE
}

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  usage
  exit 0
fi

cd "$REPO_ROOT"

# Check if this is a Node.js project
if [[ -f "package.json" ]]; then
  echo "📦 Node.js project detected - running npm build..."

  clean_flag=false
  if [[ "${1:-}" == "--clean" ]]; then
    clean_flag=true
  fi

  if [[ "$clean_flag" == true && -d dist ]]; then
    rm -rf dist
  fi

  npm run build
  echo "✅ Local build finished. Output: ${REPO_ROOT}/dist"

# Check if this is a PHP project
elif [[ -f "index.php" ]] || [[ -f "config.php" ]]; then
  echo "🐘 PHP project detected"

  # Check if PHP is available for syntax validation
  if command -v php &> /dev/null; then
    echo "   Running syntax validation..."

    # Find all PHP files and check syntax
    error_count=0
    while IFS= read -r -d '' file; do
      if ! php -l "$file" > /dev/null 2>&1; then
        echo "   ❌ Syntax error in: $file"
        ((error_count++))
      fi
    done < <(find . -name "*.php" -not -path "*/vendor/*" -not -path "*/node_modules/*" -print0)

    if [[ $error_count -gt 0 ]]; then
      echo "❌ Found $error_count PHP syntax error(s)"
      exit 1
    fi

    echo "✅ PHP syntax validation passed"
  else
    echo "   ⚠️  PHP not installed locally - skipping syntax validation"
    echo "✅ Pre-release check completed (PHP will be validated on deployment)"
  fi

else
  echo "⚠️  Unknown project type - skipping build validation"
fi
