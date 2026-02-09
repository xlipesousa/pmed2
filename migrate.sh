#!/bin/bash

################################################################################
# PMED2 - Migracao de ambiente dev para producao
# 
# Este script prepara a instalacao em /var/www/pmed2 usando o install.sh atual.
################################################################################

set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_message() {
    echo -e "${BLUE}[PMED2]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

read_env_value() {
    local key="$1"
    local value
    value=$(grep -E "^${key}=" .env | tail -n 1 | cut -d= -f2-)
    value=${value%"}
    value=${value#"}
    value=${value%\'}
    value=${value#\'}
    printf '%s' "$value"
}

if [[ "$EUID" -ne 0 ]]; then
    print_error "Este script precisa ser executado como root (use sudo)"
    exit 1
fi

SOURCE_DIR=$(pwd)
APP_DIR="/var/www/pmed2"
INSTALL_SCRIPT="$SOURCE_DIR/install.sh"

print_message "======================================"
print_message "PMED2 - Migracao dev -> producao"
print_message "======================================"

if [[ ! -f "$SOURCE_DIR/artisan" || ! -f "$SOURCE_DIR/composer.json" ]]; then
    print_error "Execute este script na raiz do projeto PMED2 (modo dev)"
    exit 1
fi

if [[ ! -f "$SOURCE_DIR/.env" ]]; then
    print_error "Arquivo .env nao encontrado em $SOURCE_DIR"
    exit 1
fi

if [[ ! -f "$INSTALL_SCRIPT" ]]; then
    print_error "install.sh nao encontrado em $SOURCE_DIR"
    exit 1
fi

DB_NAME=$(read_env_value DB_DATABASE)
DB_USER=$(read_env_value DB_USERNAME)
DB_PASS=$(read_env_value DB_PASSWORD)
APP_URL=$(read_env_value APP_URL)

if [[ -z "$DB_NAME" ]]; then
    DB_NAME="pmed2"
fi

if [[ -z "$DB_USER" ]]; then
    DB_USER="pmeduser"
fi

if [[ -z "$DB_PASS" ]]; then
    read -rsp "Senha do banco de dados: " DB_PASS
    echo
fi

DOMAIN="localhost"
USE_HTTPS=0
if [[ -n "$APP_URL" ]]; then
    if [[ "$APP_URL" =~ ^https:// ]]; then
        USE_HTTPS=1
        DOMAIN=${APP_URL#https://}
    elif [[ "$APP_URL" =~ ^http:// ]]; then
        DOMAIN=${APP_URL#http://}
    else
        DOMAIN=$APP_URL
    fi
    DOMAIN=${DOMAIN%%/*}
    DOMAIN=${DOMAIN%%:*}
fi

if [[ -z "$APP_URL" || "$DOMAIN" == "localhost" ]]; then
    print_warning "APP_URL atual aponta para localhost ou esta vazio."
    read -p "Informe o dominio/IP publico para APP_URL (ex: 10.0.0.10): " -r input
    if [[ -n "$input" ]]; then
        DOMAIN=$input
        APP_URL="http://$DOMAIN"
    fi
fi

if systemctl is-active --quiet apache2 2>/dev/null; then
    print_warning "Apache esta ativo e ocupa a porta 80."
    read -p "Deseja parar o apache2 agora? (s/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        systemctl stop apache2
        print_success "Apache parado"
        read -p "Deseja desabilitar o apache2 no boot? (s/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Ss]$ ]]; then
            systemctl disable apache2
            print_success "Apache desabilitado"
        fi
    else
        print_error "Migracao cancelada. Apache precisa liberar a porta 80."
        exit 1
    fi
fi

if ss -ltnp | grep -q ":8000"; then
    print_warning "Porta 8000 em uso (provavel artisan serve)."
    read -p "Deseja parar o processo na porta 8000? (s/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        if systemctl is-active --quiet artisan.service 2>/dev/null; then
            systemctl stop artisan.service || true
            systemctl disable artisan.service || true
        fi
        if command -v fuser >/dev/null 2>&1; then
            fuser -k 8000/tcp || true
        else
            pkill -f "artisan serve" || true
        fi
        print_success "Processo na porta 8000 finalizado"
    else
        print_error "Migracao cancelada pelo usuario"
        exit 1
    fi
fi

BACKUP_DIR="/var/backups/pmed2-migration-$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
cp "$SOURCE_DIR/.env" "$BACKUP_DIR/.env"
if [[ -d "$SOURCE_DIR/storage" ]]; then
    tar -czf "$BACKUP_DIR/storage.tar.gz" -C "$SOURCE_DIR" storage
fi
print_success "Backup criado em $BACKUP_DIR"

if [[ -d "$APP_DIR" && -n "$(ls -A "$APP_DIR" 2>/dev/null)" ]]; then
    print_warning "Diretorio $APP_DIR ja existe e nao esta vazio."
    read -p "Continuar e sobrescrever arquivos do app? (s/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Ss]$ ]]; then
        print_error "Migracao cancelada pelo usuario"
        exit 1
    fi
fi

mkdir -p "$APP_DIR"
cp "$SOURCE_DIR/.env" "$APP_DIR/.env"

print_message "Iniciando install.sh com dados do ambiente atual..."
INSTALL_CMD=("$INSTALL_SCRIPT" "--app-dir=$APP_DIR" "--db-name=$DB_NAME" "--db-user=$DB_USER" "--db-pass=$DB_PASS" "--domain=$DOMAIN")

if [[ -n "$APP_URL" ]]; then
    INSTALL_CMD+=("--app-url=$APP_URL")
elif [[ $USE_HTTPS -eq 1 ]]; then
    INSTALL_CMD+=("--https")
fi

"${INSTALL_CMD[@]}"

print_success "Migracao concluida"
print_message "Verifique o acesso via Nginx e valide o login."
print_message "Se algo der errado, use o backup em: $BACKUP_DIR"
