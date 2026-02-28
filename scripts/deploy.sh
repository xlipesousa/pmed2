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
APP_USER="${APP_USER:-admin21ct}"
WEB_GROUP="${WEB_GROUP:-www-data}"
WEB_USER="${WEB_USER:-www-data}"

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

APP_KEY_VALUE="$(grep -E '^APP_KEY=' "$SHARED/.env" | tail -n1 | cut -d'=' -f2- || true)"
if [[ -z "$APP_KEY_VALUE" || "$APP_KEY_VALUE" == "base64:" || "$APP_KEY_VALUE" == "base64:COLOQUE_AQUI_A_CHAVE" ]]; then
  echo "Erro: APP_KEY inválida em $SHARED/.env."
  echo "Defina uma chave real (ex.: php artisan key:generate --show) antes do deploy."
  exit 1
fi

if ! APP_KEY_VALUE="$APP_KEY_VALUE" php -r '
$key = trim(getenv("APP_KEY_VALUE") ?: "");
if (!str_starts_with($key, "base64:")) { exit(1); }
$raw = base64_decode(substr($key, 7), true);
if ($raw === false || strlen($raw) !== 32) { exit(1); }
'; then
  echo "Erro: APP_KEY com formato/tamanho inválido em $SHARED/.env (esperado: base64 com 32 bytes)."
  exit 1
fi

mkdir -p \
  "$RELEASE_DIR/bootstrap/cache" \
  "$SHARED/storage" \
  "$SHARED/storage/app/public" \
  "$SHARED/storage/framework" \
  "$SHARED/storage/framework/views" \
  "$SHARED/storage/framework/cache" \
  "$SHARED/storage/framework/sessions" \
  "$SHARED/storage/framework/testing" \
  "$SHARED/storage/logs"

touch "$SHARED/storage/logs/laravel.log"

if [[ ! -f "$SHARED/storage/app/public/logo.png" ]]; then
  if [[ -f "$RELEASE_DIR/public/img/logo.png" ]]; then
    cp "$RELEASE_DIR/public/img/logo.png" "$SHARED/storage/app/public/logo.png"
  elif [[ -f "$RELEASE_DIR/public/vendor/adminlte/dist/img/AdminLTELogo.png" ]]; then
    cp "$RELEASE_DIR/public/vendor/adminlte/dist/img/AdminLTELogo.png" "$SHARED/storage/app/public/logo.png"
  fi
fi

if [[ "${EUID}" -eq 0 ]]; then
  chown -R "$APP_USER:$WEB_GROUP" "$SHARED/storage" "$RELEASE_DIR/bootstrap/cache"
elif sudo -n true 2>/dev/null; then
  sudo -n chown -R "$APP_USER:$WEB_GROUP" "$SHARED/storage" "$RELEASE_DIR/bootstrap/cache" || true
else
  echo "Aviso: sem privilégio para chown; seguindo com validação de escrita real."
fi

if chgrp -R "$WEB_GROUP" "$SHARED/storage" "$RELEASE_DIR/bootstrap/cache" 2>/dev/null; then
  :
elif sudo -n true 2>/dev/null; then
  sudo -n chgrp -R "$WEB_GROUP" "$SHARED/storage" "$RELEASE_DIR/bootstrap/cache" 2>/dev/null || true
fi

chmod -R ug+rwX "$SHARED/storage" "$RELEASE_DIR/bootstrap/cache" 2>/dev/null || true
find "$SHARED/storage" "$RELEASE_DIR/bootstrap/cache" -type d -exec chmod g+s {} + 2>/dev/null || true

for path in \
  "$SHARED/storage" \
  "$SHARED/storage/framework/views" \
  "$SHARED/storage/logs" \
  "$SHARED/storage/app/public" \
  "$RELEASE_DIR/bootstrap/cache"; do
  if [[ ! -w "$path" ]]; then
    echo "Erro: sem permissão de escrita em $path"
    echo "Abortei o deploy para evitar release com erro 500 por permissão."
    exit 1
  fi
done

for path in \
  "$SHARED/storage" \
  "$SHARED/storage/framework/views" \
  "$SHARED/storage/logs" \
  "$SHARED/storage/app/public" \
  "$RELEASE_DIR/bootstrap/cache" \
  "$SHARED/storage/logs/laravel.log"; do
  if [[ ! -e "$path" ]]; then
    echo "Erro: caminho obrigatório não existe: $path"
    exit 1
  fi

  group_name="$(stat -c %G "$path")"
  if [[ "$group_name" != "$WEB_GROUP" ]]; then
    echo "Erro: grupo de $path é '$group_name' e deveria ser '$WEB_GROUP'."
    echo "Ajuste ownership/grupo para o runtime web antes do deploy."
    exit 1
  fi
done

if command -v sudo >/dev/null 2>&1 && sudo -n -u "$WEB_USER" true 2>/dev/null; then
  if ! sudo -n -u "$WEB_USER" bash -lc "for p in '$SHARED/storage/logs' '$SHARED/storage/framework/views' '$RELEASE_DIR/bootstrap/cache'; do test -w \"$p\" || exit 1; done"; then
    echo "Erro: usuário web '$WEB_USER' não consegue escrever em storage/bootstrap/cache."
    exit 1
  fi
fi

"$SCRIPTS/backup.sh"

cd "$RELEASE_DIR"
php artisan storage:link || true
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder --force
php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true

PREVIOUS_TARGET=""
if [[ -L "$APP_BASE/current" ]]; then
  PREVIOUS_TARGET="$(readlink -f "$APP_BASE/current" || true)"
fi

ln -sfn "$RELEASE_DIR" "$APP_BASE/current"

if sudo -n systemctl reload php8.3-fpm 2>/dev/null; then
  echo "php8.3-fpm recarregado com sudo"
elif systemctl reload php8.3-fpm 2>/dev/null; then
  echo "php8.3-fpm recarregado sem sudo"
else
  echo "Aviso: não foi possível recarregar php8.3-fpm automaticamente (sudo/systemctl)."
fi

if command -v curl >/dev/null 2>&1; then
  if ! curl -fsS http://127.0.0.1/health >/dev/null; then
    echo "Erro: healthcheck falhou após deploy."
    if [[ -n "$PREVIOUS_TARGET" && -d "$PREVIOUS_TARGET" ]]; then
      ln -sfn "$PREVIOUS_TARGET" "$APP_BASE/current"
      if sudo -n systemctl reload php8.3-fpm 2>/dev/null; then
        echo "Rollback automático aplicado para $PREVIOUS_TARGET."
      elif systemctl reload php8.3-fpm 2>/dev/null; then
        echo "Rollback automático aplicado para $PREVIOUS_TARGET."
      fi
    fi
    exit 1
  fi
fi

echo "Deploy concluído: $TAG -> $RELEASE_DIR"
