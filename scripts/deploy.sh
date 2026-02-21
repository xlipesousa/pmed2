#!/usr/bin/env bash
set -euo pipefail

TAG="${1:?Uso: deploy.sh <tag-semver>}"
APP_BASE="/var/www/pmed2"
RELEASES="$APP_BASE/releases"
SHARED="$APP_BASE/shared"
SCRIPTS="$SHARED/scripts"
BACKUPS="$APP_BASE/backups"
NOW="$(date +%Y-%m-%d_%H%M%S)"
RELEASE_DIR="$RELEASES/$NOW"
ARTIFACT="/tmp/pmed2-${TAG}.zip"

mkdir -p "$RELEASES" "$SHARED/storage" "$SCRIPTS" "$BACKUPS"

if [[ ! -f "$ARTIFACT" ]]; then
  echo "Artefato não encontrado: $ARTIFACT"
  exit 1
fi

mkdir -p "$RELEASE_DIR"
unzip -q "$ARTIFACT" -d "$RELEASE_DIR"

ln -sfn "$SHARED/.env" "$RELEASE_DIR/.env"
rm -rf "$RELEASE_DIR/storage"
ln -sfn "$SHARED/storage" "$RELEASE_DIR/storage"

if grep -q '^LOG_CHANNEL=' "$SHARED/.env"; then
  sed -i 's/^LOG_CHANNEL=.*/LOG_CHANNEL=daily/' "$SHARED/.env"
else
  echo 'LOG_CHANNEL=daily' >> "$SHARED/.env"
fi

mkdir -p "$RELEASE_DIR/bootstrap/cache" "$SHARED/storage"
chown -R www-data:www-data "$SHARED/storage" "$RELEASE_DIR/bootstrap/cache"
chmod -R ug+rwX "$SHARED/storage" "$RELEASE_DIR/bootstrap/cache"

"$SCRIPTS/backup.sh"

cd "$RELEASE_DIR"
php artisan migrate --force
php artisan config:cache
php artisan route:cache || true
php artisan view:cache

ln -sfn "$RELEASE_DIR" "$APP_BASE/current"
systemctl reload php8.3-fpm

echo "Deploy concluído: $TAG -> $RELEASE_DIR"
