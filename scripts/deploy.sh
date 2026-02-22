#!/usr/bin/env bash
set -euo pipefail
umask 0002

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

mkdir -p \
  "$RELEASE_DIR/bootstrap/cache" \
  "$SHARED/storage" \
  "$SHARED/storage/framework" \
  "$SHARED/storage/framework/views" \
  "$SHARED/storage/framework/cache" \
  "$SHARED/storage/framework/sessions" \
  "$SHARED/storage/framework/testing" \
  "$SHARED/storage/logs"

if chgrp -R www-data "$SHARED/storage" "$RELEASE_DIR/bootstrap/cache" 2>/dev/null; then
  :
elif sudo -n chgrp -R www-data "$SHARED/storage" "$RELEASE_DIR/bootstrap/cache" 2>/dev/null; then
  :
else
  echo "Aviso: não foi possível ajustar grupo para www-data em storage/bootstrap-cache."
fi

chmod -R ug+rwX "$SHARED/storage" "$RELEASE_DIR/bootstrap/cache" 2>/dev/null || true
find "$SHARED/storage" "$RELEASE_DIR/bootstrap/cache" -type d -exec chmod g+s {} + 2>/dev/null || true

"$SCRIPTS/backup.sh"

cd "$RELEASE_DIR"
php artisan migrate --force
php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true

ln -sfn "$RELEASE_DIR" "$APP_BASE/current"

if sudo -n systemctl reload php8.3-fpm 2>/dev/null; then
  echo "php8.3-fpm recarregado com sudo"
elif systemctl reload php8.3-fpm 2>/dev/null; then
  echo "php8.3-fpm recarregado sem sudo"
else
  echo "Aviso: não foi possível recarregar php8.3-fpm automaticamente (sudo/systemctl)."
fi

echo "Deploy concluído: $TAG -> $RELEASE_DIR"
