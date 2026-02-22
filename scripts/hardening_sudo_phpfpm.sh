#!/usr/bin/env bash
set -euo pipefail

SUDOERS_FILE="/etc/sudoers.d/pmed2-deploy-phpfpm"
DEPLOY_USER="${1:-admin21ct}"
PHP_FPM_SERVICE="${2:-php8.3-fpm}"

if [[ "${EUID}" -ne 0 ]]; then
  echo "Execute como root: sudo $0 [deploy_user] [php_fpm_service]"
  exit 1
fi

echo "Aplicando hardening de sudo para usuário '${DEPLOY_USER}' no serviço '${PHP_FPM_SERVICE}'..."

cat > "${SUDOERS_FILE}" <<EOF
${DEPLOY_USER} ALL=(root) NOPASSWD:/usr/bin/systemctl reload ${PHP_FPM_SERVICE}
EOF

chmod 440 "${SUDOERS_FILE}"
visudo -cf "${SUDOERS_FILE}"

echo "Hardening aplicado com sucesso em ${SUDOERS_FILE}."
echo "Teste sugerido: sudo -n systemctl reload ${PHP_FPM_SERVICE}"
