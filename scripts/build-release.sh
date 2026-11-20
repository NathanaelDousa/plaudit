#!/bin/sh
# Builds plaudit-<version>.zip for shared hosting: upload, unzip, open in a browser.
set -e
cd "$(dirname "$0")/.."
VERSION="${1:-dev}"
OUT="$PWD/plaudit-$VERSION.zip"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

mkdir "$TMP/plaudit"
tar --exclude=.git --exclude=.env --exclude=vendor --exclude=node_modules --exclude=tests \
    --exclude='database/database.sqlite' --exclude='public/uploads' --exclude='storage/app/migrated' \
    --exclude='storage/logs/*.log' --exclude='storage/framework/sessions/*' --exclude='storage/framework/views/*' \
    --exclude='plaudit-*.zip' --exclude=.claude -cf - . | tar -xf - -C "$TMP/plaudit"
# Laravel needs these folders to exist even though their contents were left out.
mkdir -p "$TMP/plaudit/storage/framework/sessions" "$TMP/plaudit/storage/framework/views" "$TMP/plaudit/storage/framework/cache/data" "$TMP/plaudit/storage/logs"
composer install --working-dir="$TMP/plaudit" --no-dev --optimize-autoloader --no-interaction --quiet
rm -f "$OUT"
(cd "$TMP" && zip -qr "$OUT" plaudit)
echo "Built $OUT"
