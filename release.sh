#!/usr/bin/env bash
# Version bump & release script
# Usage: ./release.sh 1.0.3

set -e

VERSION="${1}"
if [ -z "$VERSION" ]; then
  echo "Usage: $0 <version>"
  echo "Example: $0 1.0.3"
  exit 1
fi

echo "📦 Preparing release v$VERSION..."

# Update version in plugin file
sed -i "s/Version:.*/Version:           $VERSION/" advanced-reading-progress-view-counter.php

# Update version in readme.txt
sed -i "s/Stable tag:.*/Stable tag: $VERSION/" readme.txt

# Add changelog entry if not exists
if ! grep -q "= $VERSION =" readme.txt; then
  sed -i "/^== Changelog ==/a\\\\n= $VERSION =\\n* Updated\\n" readme.txt
fi

# Show changes
echo ""
echo "📝 Changes:"
git diff advanced-reading-progress-view-counter.php readme.txt

echo ""
read -p "Commit and tag v$VERSION? (y/N) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
  git add advanced-reading-progress-view-counter.php readme.txt
  git commit -m "Release v$VERSION"
  git tag "v$VERSION"
  git push origin main --tags
  echo "✅ Released v$VERSION! GitHub Action will deploy to WP.org"
else
  echo "❌ Cancelled"
  git checkout -- advanced-reading-progress-view-counter.php readme.txt
fi