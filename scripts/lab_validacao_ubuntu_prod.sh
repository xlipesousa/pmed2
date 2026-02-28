#!/usr/bin/env bash
set -euo pipefail

HOST_EXPECTED="${HOST_EXPECTED:-ubuntu-prod}"
APP_BASE="${APP_BASE:-/var/www/pmed2}"
APP_USER="${APP_USER:-admin21ct}"
WEB_GROUP="${WEB_GROUP:-www-data}"
TAG="${1:-v0.1.20}"

CURRENT_HOST="$(hostname)"
if [[ "$CURRENT_HOST" != "$HOST_EXPECTED" ]]; then
  echo "ERRO: host atual '$CURRENT_HOST' != esperado '$HOST_EXPECTED'."
  echo "Abortando para evitar execução em máquina errada."
  exit 1
fi

if [[ ! -d "$APP_BASE" ]]; then
  echo "ERRO: APP_BASE não encontrado em $APP_BASE"
  exit 1
fi

if ! command -v php >/dev/null 2>&1; then
  echo "ERRO: php não encontrado no host"
  exit 1
fi

run_root() {
  if sudo -n true >/dev/null 2>&1; then
    sudo -n "$@"
  else
    sudo "$@"
  fi
}

CURRENT_RELEASE="$(readlink -f "$APP_BASE/current")"
if [[ -z "$CURRENT_RELEASE" || ! -d "$CURRENT_RELEASE" ]]; then
  echo "ERRO: release atual inválido em $APP_BASE/current"
  exit 1
fi

echo "[1/5] Ajustando ownership e permissões de runtime..."
run_root chown -R "$APP_USER:$WEB_GROUP" "$APP_BASE/shared/storage" "$CURRENT_RELEASE/bootstrap/cache"
run_root find "$APP_BASE/shared/storage" "$CURRENT_RELEASE/bootstrap/cache" -type d -exec chmod 2775 {} +
run_root find "$APP_BASE/shared/storage" "$CURRENT_RELEASE/bootstrap/cache" -type f -exec chmod 664 {} +
run_root touch "$APP_BASE/shared/storage/logs/laravel.log"
run_root chown "$APP_USER:$WEB_GROUP" "$APP_BASE/shared/storage/logs/laravel.log"
run_root chmod 664 "$APP_BASE/shared/storage/logs/laravel.log"

echo "[2/5] Executando deploy de validação ($TAG)..."
DEPLOY_SCRIPT="$APP_BASE/shared/scripts/deploy.sh"
if [[ ! -x "$DEPLOY_SCRIPT" ]]; then
  echo "ERRO: script de deploy não encontrado/executável em $DEPLOY_SCRIPT"
  exit 1
fi
"$DEPLOY_SCRIPT" "$TAG"

CURRENT_RELEASE="$(readlink -f "$APP_BASE/current")"
LOG_FILE="$(ls -1t "$APP_BASE/shared/storage/logs"/laravel-*.log | head -n1)"

echo "[3/5] Testando /login e verificando Permission denied no release atual..."
curl -sS -o /tmp/pmed2_login_validate.html -D /tmp/pmed2_login_validate.headers http://127.0.0.1/login >/dev/null
HTTP_CODE="$(awk 'NR==1{print $2}' /tmp/pmed2_login_validate.headers)"
if [[ "$HTTP_CODE" != "200" && "$HTTP_CODE" != "302" ]]; then
  echo "ERRO: /login retornou HTTP $HTTP_CODE"
  exit 1
fi

if grep -iE "permission denied|failed to open stream" "$LOG_FILE" | grep -q "$CURRENT_RELEASE"; then
  echo "ERRO: encontrado Permission denied para release atual: $CURRENT_RELEASE"
  grep -iE "permission denied|failed to open stream" "$LOG_FILE" | grep "$CURRENT_RELEASE" | tail -n 20
  exit 1
fi

echo "[4/5] Validando 2 execuções observadas do backup..."
CRON_LOG="$APP_BASE/shared/storage/logs/cron-backup.log"
run_root touch "$CRON_LOG"
run_root chown "$APP_USER:$WEB_GROUP" "$CRON_LOG"
run_root chmod 664 "$CRON_LOG"

BEFORE_COUNT="$(grep -c "Backup concluído:" "$CRON_LOG" 2>/dev/null || true)"
"$APP_BASE/shared/scripts/backup.sh" >> "$CRON_LOG" 2>&1
"$APP_BASE/shared/scripts/backup.sh" >> "$CRON_LOG" 2>&1
AFTER_COUNT="$(grep -c "Backup concluído:" "$CRON_LOG" 2>/dev/null || true)"
DELTA=$((AFTER_COUNT - BEFORE_COUNT))

if (( DELTA < 2 )); then
  echo "ERRO: não foi possível observar 2 novas execuções de backup (delta=$DELTA)."
  tail -n 40 "$CRON_LOG" || true
  exit 1
fi

echo "[5/5] Resumo final"
echo "Host: $CURRENT_HOST"
echo "Release atual: $CURRENT_RELEASE"
echo "HTTP /login: $HTTP_CODE"
echo "Arquivo de log Laravel: $LOG_FILE"
echo "Novas execuções de backup observadas: $DELTA"
echo "VALIDAÇÃO LAB CONCLUÍDA COM SUCESSO"
