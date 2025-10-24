#!/usr/bin/env bash

#############################################
# Deployment Script
#
# Deploys the built application to the production server.
#############################################

set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

REPO_PATH="${REPO_PATH:-/Volumes/DatenAP/Code/admin.arkturian.com}"
DEPLOY_PATH="${DEPLOY_PATH:-/var/www/admin.arkturian.com}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups}"
BACKUP_PREFIX="${BACKUP_PREFIX:-admin-panel}"
TIMESTAMP=$(date +%Y%m%d-%H%M%S)
BACKUP_PATH="$BACKUP_DIR/${BACKUP_PREFIX}-${TIMESTAMP}"

echo -e "${GREEN}🚀 Starting deployment process...${NC}"

echo -e "${YELLOW}📂 Navigating to repository...${NC}"
cd "$REPO_PATH"

echo -e "${YELLOW}⬇️  Pulling latest changes from main...${NC}"
git fetch origin main
git reset --hard origin/main

echo -e "${YELLOW}🔍 Validating PHP files...${NC}"
if command -v php >/dev/null 2>&1; then
  php -v
  echo "PHP syntax validation..."
  find . -name "*.php" -not -path "./.git/*" -exec php -l {} \; > /dev/null 2>&1 || {
    echo -e "${RED}❌ PHP syntax errors found${NC}"
    exit 1
  }
  echo -e "${GREEN}✅ PHP validation passed${NC}"
else
  echo -e "${YELLOW}⚠️  PHP not found, skipping validation${NC}"
fi

if [ -d "$DEPLOY_PATH" ]; then
  echo -e "${YELLOW}💾 Creating backup...${NC}"
  mkdir -p "$BACKUP_DIR"
  cp -r "$DEPLOY_PATH" "$BACKUP_PATH"
  echo -e "${GREEN}✅ Backup created: $BACKUP_PATH${NC}"
else
  echo -e "${YELLOW}⚠️  No existing deployment found, skipping backup${NC}"
fi

echo -e "${YELLOW}🚢 Deploying PHP application...${NC}"
mkdir -p "$DEPLOY_PATH"
rsync -av --delete \
  --exclude='.git' \
  --exclude='.devops' \
  --exclude='.github' \
  --exclude='*.md' \
  --exclude='.DS_Store' \
  --exclude='*.backup' \
  "$REPO_PATH/" "$DEPLOY_PATH/"

echo -e "${YELLOW}🔒 Setting permissions...${NC}"
chown -R www-data:www-data "$DEPLOY_PATH"
chmod -R 755 "$DEPLOY_PATH"

echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo -e "${GREEN}📍 Deployed to: $DEPLOY_PATH${NC}"
echo -e "${GREEN}💾 Backup saved: $BACKUP_PATH${NC}"
echo -e "${GREEN}🕒 Timestamp: $TIMESTAMP${NC}"

echo -e "\n${GREEN}📊 Deployment Summary:${NC}"
echo -e "  Repository: $REPO_PATH"
echo -e "  Deployment: $DEPLOY_PATH"
echo -e "  Backup: $BACKUP_PATH"
echo -e "  Files deployed:"
ls -lh "$DEPLOY_PATH" | tail -n +2
