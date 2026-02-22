#!/usr/bin/env bash
set -euo pipefail

APP_BASE="${APP_BASE:-/var/www/pmed2}"
SCHEDULE="${1:-0 2 * * *}"
CRON_FILE="/etc/cron.d/pmed2-backup"
RUN_USER="${2:-admin21ct}"
BACKUP_SCRIPT="$APP_BASE/shared/scripts/backup.sh"
LOG_FILE="$APP_BASE/shared/storage/logs/cron-backup.log"

if [[ "${EUID}" -ne 0 ]]; then
  echo "Execute como root: sudo $0 [schedule] [run_user]"
  exit 1
fi

if [[ ! -x "$BACKUP_SCRIPT" ]]; then
  echo "Script de backup não encontrado ou sem permissão de execução: $BACKUP_SCRIPT"
  exit 1
fi

mkdir -p "$(dirname "$LOG_FILE")"
chown -R "$RUN_USER":www-data "$APP_BASE/shared/storage" 2>/dev/null || true

cat > "$CRON_FILE" <<EOF
SHELL=/bin/bash
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

${SCHEDULE} ${RUN_USER} ${BACKUP_SCRIPT} >> ${LOG_FILE} 2>&1
EOF

chmod 644 "$CRON_FILE"

echo "Cron configurado em ${CRON_FILE}:"
cat "$CRON_FILE"

echo "Executando teste imediato do backup..."
sudo -u "$RUN_USER" "$BACKUP_SCRIPT"

echo "Backup diário configurado e teste concluído."
