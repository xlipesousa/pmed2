#!/usr/bin/env bash
set -euo pipefail

APP_BASE="${APP_BASE:-/var/www/pmed2}"
SHARED="$APP_BASE/shared"
SCRIPTS="$SHARED/scripts"
BACKUPS="$APP_BASE/backups"
ENV_FILE="$SHARED/.env"

TAG="${1:-}"
RESTORE_FILE="${2:-}"

if [[ -z "$TAG" ]]; then
  echo "Uso: $0 <tag> [caminho_backup_sql_gz]"
  echo "Exemplo: $0 v0.1.13 /var/www/pmed2/backups/db_pmed2_2026-02-22_165139.sql.gz"
  exit 1
fi

if [[ ! -x "$SCRIPTS/deploy.sh" || ! -x "$SCRIPTS/rollback.sh" || ! -x "$SCRIPTS/backup.sh" ]]; then
  echo "Scripts operacionais ausentes em $SCRIPTS"
  exit 1
fi

echo "[1/4] Deploy da tag $TAG"
"$SCRIPTS/deploy.sh" "$TAG"

echo "[2/4] Healthcheck"
curl -fsS http://127.0.0.1/health >/dev/null
echo "Healthcheck OK"

echo "[3/4] Rollback controlado"
"$SCRIPTS/rollback.sh"
curl -fsS http://127.0.0.1/health >/dev/null
echo "Rollback OK"

if [[ -n "$RESTORE_FILE" ]]; then
  if [[ ! -f "$RESTORE_FILE" ]]; then
    echo "Arquivo de restore não encontrado: $RESTORE_FILE"
    exit 1
  fi

  echo "[4/4] Restore controlado de banco"

  DB_HOST="$(grep -E '^DB_HOST=' "$ENV_FILE" | tail -n1 | cut -d= -f2-)"
  DB_PORT="$(grep -E '^DB_PORT=' "$ENV_FILE" | tail -n1 | cut -d= -f2-)"
  DB_DATABASE="$(grep -E '^DB_DATABASE=' "$ENV_FILE" | tail -n1 | cut -d= -f2-)"
  DB_USERNAME="$(grep -E '^DB_USERNAME=' "$ENV_FILE" | tail -n1 | cut -d= -f2-)"
  DB_PASSWORD="$(grep -E '^DB_PASSWORD=' "$ENV_FILE" | tail -n1 | cut -d= -f2-)"

  DB_HOST="${DB_HOST:-127.0.0.1}"
  DB_PORT="${DB_PORT:-3306}"

  export MYSQL_PWD="$DB_PASSWORD"
  if [[ "$RESTORE_FILE" == *.gz ]]; then
    gzip -dc "$RESTORE_FILE" | mysql --binary-mode -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "$DB_DATABASE"
  else
    mysql --binary-mode -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "$DB_DATABASE" < "$RESTORE_FILE"
  fi
  unset MYSQL_PWD

  echo "Restore concluído"
fi

echo "Bateria operacional concluída com sucesso."
