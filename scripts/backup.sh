#!/usr/bin/env bash
set -euo pipefail

APP_BASE="/var/www/pmed2"
BACKUPS="$APP_BASE/backups"
DATE="$(date +%Y-%m-%d_%H%M%S)"

set -a
source "$APP_BASE/shared/.env"
set +a

mkdir -p "$BACKUPS"

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:?DB_DATABASE não definido}"
DB_USERNAME="${DB_USERNAME:?DB_USERNAME não definido}"
DB_PASSWORD="${DB_PASSWORD:?DB_PASSWORD não definido}"

export MYSQL_PWD="$DB_PASSWORD"
mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "$DB_DATABASE" | gzip > "$BACKUPS/db_${DB_DATABASE}_${DATE}.sql.gz"
unset MYSQL_PWD

ls -1t "$BACKUPS"/db_*.sql.gz | tail -n +15 | xargs -r rm -f

echo "Backup concluído: db_${DB_DATABASE}_${DATE}.sql.gz"
