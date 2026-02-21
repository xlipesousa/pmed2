#!/usr/bin/env bash
set -euo pipefail

APP_BASE="/var/www/pmed2"
RELEASES="$APP_BASE/releases"

CURRENT_TARGET="$(readlink -f "$APP_BASE/current" || true)"
PREV_RELEASE="$(ls -1dt "$RELEASES"/* | grep -v "^${CURRENT_TARGET}$" | head -n1)"

if [[ -z "${PREV_RELEASE:-}" ]]; then
  echo "Sem release anterior para rollback."
  exit 1
fi

ln -sfn "$PREV_RELEASE" "$APP_BASE/current"
systemctl reload php8.3-fpm

echo "Rollback concluído para: $PREV_RELEASE"
echo "Se necessário, execute restore de banco com backup validado."
