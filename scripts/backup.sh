#!/usr/bin/env bash
set -euo pipefail

APP_BASE="/var/www/pmed2"
BACKUPS="$APP_BASE/backups"
DATE="$(date +%Y-%m-%d_%H%M%S)"

get_env_value() {
	local key="$1"
	local file="$2"
	local line

	line="$(grep -E "^${key}=" "$file" | tail -n 1 || true)"
	line="${line#*=}"

	if [[ "$line" == '"'*'"' ]]; then
		line="${line:1:${#line}-2}"
	elif [[ "$line" == "'"*"'" ]]; then
		line="${line:1:${#line}-2}"
	fi

	printf '%s' "$line"
}

mkdir -p "$BACKUPS"

ENV_FILE="$APP_BASE/shared/.env"
DB_HOST="$(get_env_value DB_HOST "$ENV_FILE")"
DB_PORT="$(get_env_value DB_PORT "$ENV_FILE")"
DB_DATABASE="$(get_env_value DB_DATABASE "$ENV_FILE")"
DB_USERNAME="$(get_env_value DB_USERNAME "$ENV_FILE")"
DB_PASSWORD="$(get_env_value DB_PASSWORD "$ENV_FILE")"

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"

if [[ "$DB_HOST" == "localhost" ]]; then
	DB_HOST="127.0.0.1"
fi

[[ -n "$DB_DATABASE" ]] || { echo "DB_DATABASE não definido"; exit 1; }
[[ -n "$DB_USERNAME" ]] || { echo "DB_USERNAME não definido"; exit 1; }
[[ -n "$DB_PASSWORD" ]] || { echo "DB_PASSWORD não definido"; exit 1; }

export MYSQL_PWD="$DB_PASSWORD"
if ! mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -e "SELECT 1" >/dev/null 2>&1; then
	echo "Falha de autenticação/conexão MySQL para backup (host=$DB_HOST, port=$DB_PORT, user=$DB_USERNAME)."
	echo "Verifique DB_HOST/DB_PORT/DB_USERNAME/DB_PASSWORD no /var/www/pmed2/shared/.env e grants no MySQL."
	unset MYSQL_PWD
	exit 2
fi
mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "$DB_DATABASE" | gzip > "$BACKUPS/db_${DB_DATABASE}_${DATE}.sql.gz"
unset MYSQL_PWD

ls -1t "$BACKUPS"/db_*.sql.gz | tail -n +15 | xargs -r rm -f

echo "Backup concluído: db_${DB_DATABASE}_${DATE}.sql.gz"
