#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ -n "${DB_PASSWORD_FILE:-}" ] && [ -f "${DB_PASSWORD_FILE}" ]; then
  DB_PASSWORD="$(cat "${DB_PASSWORD_FILE}")"
  export DB_PASSWORD
fi

mkdir -p \
  storage/app/public \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

# Fallback de logo: se o arquivo existir na raiz, sincroniza para os locais usados na UI.
if [ -f "logo.png" ]; then
  mkdir -p public/img storage/app/public
  if [ ! -f "public/img/logo.png" ]; then
    cp "logo.png" "public/img/logo.png"
  fi
  if [ ! -f "storage/app/public/logo.png" ]; then
    cp "logo.png" "storage/app/public/logo.png"
  fi
fi

if [ "${RUN_STORAGE_LINK:-true}" = "true" ]; then
  php artisan storage:link >/dev/null 2>&1 || true
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force
fi

exec "$@"
