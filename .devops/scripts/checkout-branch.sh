#!/usr/bin/env bash

set -euo pipefail

REPO_ROOT="/Volumes/DatenAP/Code/admin.arkturian.com"
DEV_BRANCH="dev"
MAIN_BRANCH="main"

usage() {
  cat <<'USAGE'
Usage: checkout-branch.sh <branch>

Switches the working tree to the requested branch and fast-forwards it from origin.

Examples:
  checkout-branch.sh dev
  checkout-branch.sh main
USAGE
}

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  usage
  exit 0
fi

if [[ $# -ne 1 ]]; then
  usage
  exit 1
fi

branch="$1"

if [[ "$branch" != "$DEV_BRANCH" && "$branch" != "$MAIN_BRANCH" ]]; then
  echo "Error: branch must be '$DEV_BRANCH' or '$MAIN_BRANCH'." >&2
  exit 1
fi

cd "$REPO_ROOT"

if [[ -n "$(git status --porcelain)" ]]; then
  echo "Error: working tree has uncommitted changes. Please commit or stash them first." >&2
  exit 1
fi

git fetch origin "$branch"
git checkout "$branch"

# Check if local is ahead, behind, or diverged
LOCAL=$(git rev-parse @)
REMOTE=$(git rev-parse @{u} 2>/dev/null || echo "")
BASE=$(git merge-base @ @{u} 2>/dev/null || echo "")

if [ -z "$REMOTE" ]; then
  echo "⚠️  No remote tracking branch. Branch is local-only."
elif [ "$LOCAL" = "$REMOTE" ]; then
  echo "✅ Branch is up-to-date with origin"
elif [ "$LOCAL" = "$BASE" ]; then
  # Local is behind, can fast-forward
  git pull --ff-only origin "$branch"
  echo "✅ Fast-forwarded to origin/$branch"
elif [ "$REMOTE" = "$BASE" ]; then
  # Local is ahead, push first
  echo "⚠️  Local branch is ahead of origin. Pushing first..."
  git push origin "$branch"
  echo "✅ Pushed local commits to origin/$branch"
else
  # Diverged
  echo "❌ Error: Local and remote have diverged. Manual merge required." >&2
  exit 1
fi

echo "✅ Checked out $(git rev-parse --abbrev-ref HEAD) at $(git rev-parse --short HEAD)"
