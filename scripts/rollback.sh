#!/usr/bin/env bash
# WARNING: DEPRECATED for bare-metal operation.
# Preferred path: Docker Compose + CI/CD workflows.
# Planned removal window: 2026-06 (Phase F).
set -euo pipefail

echo "[DEPRECATED] rollback.sh e contingencia bare-metal. Priorize fluxo Docker/CI-CD." >&2

APP_BASE="/var/www/pmed2"
RELEASES="$APP_BASE/releases"

CURRENT_TARGET="$(readlink -f "$APP_BASE/current" || true)"
PREV_RELEASE="$(ls -1dt "$RELEASES"/* | grep -v "^${CURRENT_TARGET}$" | head -n1)"

if [[ -z "${PREV_RELEASE:-}" ]]; then
  echo "Sem release anterior para rollback."
  exit 1
fi

ln -sfn "$PREV_RELEASE" "$APP_BASE/current"

if sudo -n systemctl reload php8.3-fpm 2>/dev/null; then
  echo "php8.3-fpm recarregado com sudo"
else
  echo "Aviso: não foi possível recarregar php8.3-fpm automaticamente (sudo -n)."
fi

echo "Rollback concluído para: $PREV_RELEASE"
echo "Se necessário, execute restore de banco com backup validado."
