#!/bin/bash

################################################################################
# PMED2 - Sistema de Gestao de Faturas Hospitalares
# Script de Instalacao Automatizada (refatorado)
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

usage() {
    cat <<EOF
Uso: sudo ./install.sh [opcoes]

Opcoes:
  --db-name=NAME        Nome do banco (default: pmed2)
    --db-user=USER        Usuario do banco (default: pmeduser)
  --db-pass=PASS        Senha do banco (obrigatorio se nao interativo)
  --domain=HOST         Dominio/IP (default: localhost)
  --app-dir=DIR         Diretorio do app (default: /var/www/pmed2)
    --with-nginx          Configura Nginx + PHP-FPM (padrao: ativado)
    --app-url=URL         Define APP_URL manualmente
    --https               Usa https:// no APP_URL (se --app-url nao for usado)
    --skip-update         Nao executa apt update
    --skip-upgrade        Nao executa apt upgrade
  -h, --help            Mostra esta ajuda
EOF
}

trap 'print_error "Erro na linha $LINENO. Abortando."' ERR

escape_sed_replacement() {
    local value="$1"
    value="${value//\\/\\\\}"
    value="${value//&/\\&}"
    value="${value//\//\\/}"
    printf '%s' "$value"
}

escape_mysql_string() {
    local value="$1"
    value="${value//\\/\\\\}"
    value="${value//\'/\\\'}"
    printf '%s' "$value"
}

# Defaults
APP_DIR="/var/www/pmed2"
DOMAIN="localhost"
DB_NAME="pmed2"
DB_USER="pmeduser"
DB_PASS=""
APP_URL_OVERRIDE=""
USE_HTTPS=0
SKIP_UPDATE=0
SKIP_UPGRADE=0

# Parse args
while [[ $# -gt 0 ]]; do
    case "$1" in
        --db-name=*) DB_NAME="${1#*=}"; shift ;;
        --db-name) DB_NAME="${2:-}"; shift 2 ;;
        --db-user=*) DB_USER="${1#*=}"; shift ;;
        --db-user) DB_USER="${2:-}"; shift 2 ;;
        --db-pass=*) DB_PASS="${1#*=}"; shift ;;
        --db-pass) DB_PASS="${2:-}"; shift 2 ;;
        --domain=*) DOMAIN="${1#*=}"; shift ;;
        --domain) DOMAIN="${2:-}"; shift 2 ;;
        --app-dir=*) APP_DIR="${1#*=}"; shift ;;
        --app-dir) APP_DIR="${2:-}"; shift 2 ;;
        --with-nginx) shift ;;
        --app-url=*) APP_URL_OVERRIDE="${1#*=}"; shift ;;
        --app-url) APP_URL_OVERRIDE="${2:-}"; shift 2 ;;
        --https) USE_HTTPS=1; shift ;;
        --skip-update) SKIP_UPDATE=1; shift ;;
        --skip-upgrade) SKIP_UPGRADE=1; shift ;;
        -h|--help) usage; exit 0 ;;
        *)
            print_error "Opcao desconhecida: $1"
            usage
            exit 1
            ;;
    esac
done

if [[ "$EUID" -ne 0 ]]; then
    print_error "Este script precisa ser executado como root (use sudo)"
    exit 1
fi

print_message "======================================"
print_message "PMED2 - Instalacao do Sistema"
print_message "======================================"

# Detectar distribuicao
if [[ -f /etc/os-release ]]; then
    . /etc/os-release
    OS=$NAME
    VER=$VERSION_ID
else
    print_error "Nao foi possivel detectar a distribuicao Linux"
    exit 1
fi

print_message "Sistema detectado: $OS $VER"

# Verificar se e Ubuntu/Debian
if [[ "$OS" != *"Ubuntu"* ]] && [[ "$OS" != *"Debian"* ]]; then
    print_warning "Este script foi testado apenas em Ubuntu/Debian"
    if [[ -t 0 ]]; then
        read -p "Deseja continuar? (s/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Ss]$ ]]; then
            exit 1
        fi
    else
        print_error "Execucao nao interativa em OS nao suportado"
        exit 1
    fi
fi

# Pre-checks do projeto
SOURCE_DIR=$(pwd)
if [[ ! -f "$SOURCE_DIR/composer.json" || ! -f "$SOURCE_DIR/artisan" ]]; then
    print_error "Execute o script no diretorio raiz do projeto"
    exit 1
fi

# Coletar parametros se necessario
if [[ -t 0 ]]; then
    read -p "Nome do banco de dados [${DB_NAME}]: " input
    DB_NAME=${input:-$DB_NAME}

    read -p "Usuario do banco de dados [${DB_USER}]: " input
    DB_USER=${input:-$DB_USER}

    if [[ -z "$DB_PASS" ]]; then
        read -rsp "Senha do banco de dados: " DB_PASS
        echo
    fi

    read -p "Dominio ou IP [${DOMAIN}]: " input
    DOMAIN=${input:-$DOMAIN}
else
    if [[ -z "$DB_PASS" ]]; then
        print_error "DB_PASS obrigatorio em modo nao interativo"
        exit 1
    fi
fi

if [[ ! "$DB_NAME" =~ ^[A-Za-z0-9_]+$ ]]; then
    print_error "DB_NAME invalido. Use apenas letras, numeros e underscore."
    exit 1
fi

if [[ ! "$DB_USER" =~ ^[A-Za-z0-9_]+$ ]]; then
    print_error "DB_USER invalido. Use apenas letras, numeros e underscore."
    exit 1
fi

if [[ -z "$DB_PASS" ]]; then
    print_error "DB_PASS nao pode ser vazio"
    exit 1
fi

print_message "======================================"
print_message "Etapa 1: Atualizacao do Sistema"
print_message "======================================"

APT_UPDATE_ALLOWED=1
if [[ "$SKIP_UPDATE" -eq 1 ]]; then
    print_warning "apt update ignorado por flag"
    APT_UPDATE_ALLOWED=0
elif [[ -t 0 ]]; then
    read -p "Rodar apt update? (S/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Nn]$ ]]; then
        apt update
    else
        print_warning "apt update ignorado por escolha do usuario"
        APT_UPDATE_ALLOWED=0
    fi
else
    print_warning "Modo nao interativo: apt update ignorado (use --skip-update para suprimir aviso)"
    APT_UPDATE_ALLOWED=0
fi

if [[ "$SKIP_UPGRADE" -eq 1 ]]; then
    print_warning "apt upgrade ignorado por flag"
elif [[ -t 0 ]]; then
    read -p "Rodar apt upgrade -y? (s/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        apt upgrade -y
    else
        print_warning "apt upgrade ignorado por escolha do usuario"
    fi
else
    print_warning "Modo nao interativo: apt upgrade ignorado (use --skip-upgrade para suprimir aviso)"
fi

print_success "Etapa de atualizacao concluida"

print_message "======================================"
print_message "Etapa 2: Instalacao de Dependencias"
print_message "======================================"

apt install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
if [[ "$APT_UPDATE_ALLOWED" -eq 1 ]]; then
    apt update
else
    print_warning "apt update foi ignorado; instalacao de pacotes pode falhar"
fi

apt install -y \
    nginx \
    git \
    curl \
    wget \
    unzip \
    zip \
    supervisor \
    rsync

print_success "Pacotes base instalados"

PHP_VERSION=""
if command -v php >/dev/null 2>&1; then
    PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')
fi
if [[ -z "$PHP_VERSION" ]]; then
    PHP_VERSION="8.2"
fi

print_message "Instalando PHP $PHP_VERSION e extensoes..."
apt install -y \
    "php$PHP_VERSION" \
    "php$PHP_VERSION-fpm" \
    "php$PHP_VERSION-cli" \
    "php$PHP_VERSION-mysql" \
    "php$PHP_VERSION-mbstring" \
    "php$PHP_VERSION-xml" \
    "php$PHP_VERSION-bcmath" \
    "php$PHP_VERSION-curl" \
    "php$PHP_VERSION-gd" \
    "php$PHP_VERSION-zip" \
    "php$PHP_VERSION-intl"

PHP_FPM_SERVICE="php${PHP_VERSION}-fpm"
PHP_FPM_SOCK="/var/run/php/php${PHP_VERSION}-fpm.sock"

print_success "PHP $PHP_VERSION instalado"

DB_SERVICE=""
if systemctl is-active --quiet mariadb 2>/dev/null || dpkg -s mariadb-server >/dev/null 2>&1; then
    DB_SERVICE="mariadb"
elif systemctl is-active --quiet mysql 2>/dev/null || dpkg -s mysql-server >/dev/null 2>&1; then
    DB_SERVICE="mysql"
fi

if [[ -z "$DB_SERVICE" ]]; then
    print_message "Instalando MySQL Server..."
    apt install -y mysql-server mysql-client
    DB_SERVICE="mysql"
    print_success "MySQL instalado"
else
    print_message "Banco $DB_SERVICE ja instalado, pulando instalacao"
fi

print_message "Instalando Node.js 20.x..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

print_success "Node.js instalado"

print_message "Atualizando NPM (compatibilidade com Node)..."
NODE_MAJOR=$(node -v | sed 's/^v//' | cut -d. -f1)
if [[ "$NODE_MAJOR" -ge 20 ]]; then
    npm install -g "npm@^10"
else
    npm install -g "npm@^9"
fi

print_message "Instalando Composer..."
if ! command -v composer >/dev/null 2>&1; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    chmod +x /usr/local/bin/composer
fi

print_success "Composer instalado"

print_message "======================================"
print_message "Etapa 3: Configuracao do MySQL"
print_message "======================================"

systemctl start "$DB_SERVICE"
systemctl enable "$DB_SERVICE"

print_message "Configurando banco de dados..."
DB_USER_ESC=$(escape_mysql_string "$DB_USER")
DB_PASS_ESC=$(escape_mysql_string "$DB_PASS")

mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER_ESC}'@'localhost' IDENTIFIED BY '${DB_PASS_ESC}';"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER_ESC}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS_ESC}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER_ESC}'@'localhost';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER_ESC}'@'127.0.0.1';"
mysql -e "FLUSH PRIVILEGES;"

# Testar credenciais do app antes de seguir
if ! mysql -u"$DB_USER_ESC" -p"$DB_PASS_ESC" -h127.0.0.1 -e "SELECT 1" "$DB_NAME" >/dev/null 2>&1; then
    print_error "Falha ao autenticar no MySQL com o usuario do app"
    print_error "Verifique DB_USER/DB_PASS e o host (localhost vs 127.0.0.1)"
    exit 1
fi

print_success "Banco de dados configurado"

print_message "======================================"
print_message "Etapa 4: Configuracao da Aplicacao"
print_message "======================================"

print_message "Diretorio de instalacao: $APP_DIR"

if [[ "$APP_DIR" != "$SOURCE_DIR" ]]; then
    print_message "Sincronizando codigo para $APP_DIR..."
    mkdir -p "$APP_DIR"
    rsync -a --delete \
        --exclude='.git' \
        --exclude='node_modules' \
        --exclude='vendor' \
        --exclude='.env' \
        "$SOURCE_DIR/" "$APP_DIR/"
fi

WORK_DIR="$APP_DIR"

if [[ ! -f "$WORK_DIR/composer.json" || ! -f "$WORK_DIR/artisan" ]]; then
    print_error "Diretorio de app invalido: $WORK_DIR"
    exit 1
fi

if [[ ! -f "$WORK_DIR/.env" ]]; then
    if [[ -f "$WORK_DIR/.env.example" ]]; then
        print_message "Criando arquivo .env..."
        cp "$WORK_DIR/.env.example" "$WORK_DIR/.env"
        print_success "Arquivo .env criado"
    else
        print_error "Arquivo .env.example nao encontrado"
        exit 1
    fi
else
    print_warning "Arquivo .env ja existe, mantendo configuracoes atuais"
fi

print_message "Configurando .env com dados do banco..."
DB_NAME_SED=$(escape_sed_replacement "$DB_NAME")
DB_USER_SED=$(escape_sed_replacement "$DB_USER")
DB_PASS_SED=$(escape_sed_replacement "$DB_PASS")

sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME_SED/" "$WORK_DIR/.env"
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER_SED/" "$WORK_DIR/.env"
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS_SED/" "$WORK_DIR/.env"

print_message "Instalando dependencias PHP (Composer)..."
( cd "$WORK_DIR" && COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction --prefer-dist --optimize-autoloader )
print_success "Dependencias PHP instaladas"


print_message "Instalando dependencias JavaScript (NPM)..."
if [[ -f "$WORK_DIR/package-lock.json" ]]; then
    if ( cd "$WORK_DIR" && npm ci --dry-run >/dev/null 2>&1 ); then
        ( cd "$WORK_DIR" && npm ci )
    else
        print_warning "Lockfile fora de sincronia; executando npm install"
        ( cd "$WORK_DIR" && npm install )
        print_message "Executando npm ci com lockfile atualizado..."
        ( cd "$WORK_DIR" && npm ci )
    fi
else
    ( cd "$WORK_DIR" && npm install )
fi
print_success "Dependencias JavaScript instaladas"

print_message "Gerando chave da aplicacao..."
if ! grep -q '^APP_KEY=base64:' "$WORK_DIR/.env"; then
    ( cd "$WORK_DIR" && php artisan key:generate --force )
fi
print_success "Chave da aplicacao configurada"

print_message "Executando migrations do banco de dados..."
print_message "Preparando tabela configuracoes..."
trap - ERR
set +e
( cd "$WORK_DIR" && php artisan migrate --force --path=database/migrations/2023_01_01_000008_create_configuracoes_table.php )
set -e
trap 'print_error "Erro na linha $LINENO. Abortando."' ERR

TABLE_EXISTS=$(mysql -N -B -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}' AND table_name='configuracoes';")
if [[ "$TABLE_EXISTS" -eq 1 ]]; then
    COLUMN_EXISTS=$(mysql -N -B -e "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='${DB_NAME}' AND table_name='configuracoes' AND column_name='descricao';")
    if [[ "$COLUMN_EXISTS" -eq 0 ]]; then
        print_warning "Adicionando coluna configuracoes.descricao..."
        mysql -D "$DB_NAME" -e "ALTER TABLE configuracoes ADD COLUMN descricao TEXT NULL;"
    fi
fi

( cd "$WORK_DIR" && php artisan migrate --force )
print_success "Migrations executadas"

print_message "Criando usuario administrador padrao..."
( cd "$WORK_DIR" && php artisan db:seed --class=AdminUserSeeder --force )
print_success "Usuario administrador criado"

print_message "Criando link simbolico para storage..."
( cd "$WORK_DIR" && php artisan storage:link )
print_success "Link de storage criado"

print_message "Compilando assets frontend..."
if ! ( cd "$WORK_DIR" && npm run build ); then
    print_error "Falha no npm run build"
    exit 1
fi
if [[ ! -f "$WORK_DIR/public/build/manifest.json" ]]; then
    print_error "Build nao gerou public/build/manifest.json"
    exit 1
fi
print_success "Assets compilados"

print_message "======================================"
print_message "Etapa 5: Configuracao de Permissoes"
print_message "======================================"

chown -R www-data:www-data "$WORK_DIR"
chmod -R 755 "$WORK_DIR"
chmod -R 775 "$WORK_DIR/storage" "$WORK_DIR/bootstrap/cache"

print_success "Permissoes configuradas"

print_message "Publicando assets e traducoes do AdminLTE..."
mkdir -p "$WORK_DIR/lang" "$WORK_DIR/storage/logs" "$WORK_DIR/public/vendor"
chown -R www-data:www-data "$WORK_DIR/lang" "$WORK_DIR/storage/logs" "$WORK_DIR/public/vendor"
if ( cd "$WORK_DIR" && php artisan list --format=txt | grep -q "adminlte:install" ); then
    if ! ( cd "$WORK_DIR" && sudo -u www-data php artisan adminlte:install --only=assets --force ); then
        print_warning "Falha ao publicar assets do AdminLTE"
    fi
    if ! ( cd "$WORK_DIR" && sudo -u www-data php artisan adminlte:install --only=translations --force ); then
        print_warning "Falha ao publicar traducoes do AdminLTE"
    fi
else
    print_warning "Comando adminlte:install nao encontrado"
fi

# Garantir traducoes para pt_BR quando pacote usa pt-br
ADMINLTE_LANG_BASE="$WORK_DIR/lang/vendor/adminlte"
if [[ -d "$ADMINLTE_LANG_BASE/pt-br" && ! -d "$ADMINLTE_LANG_BASE/pt_BR" ]]; then
    print_warning "Copiando traducoes AdminLTE de pt-br para pt_BR"
    cp -a "$ADMINLTE_LANG_BASE/pt-br" "$ADMINLTE_LANG_BASE/pt_BR"
    chown -R www-data:www-data "$ADMINLTE_LANG_BASE/pt_BR"
fi

# Garantir icheck-bootstrap para a tela de login
ICHECK_DIR="$WORK_DIR/public/vendor/icheck-bootstrap"
ICHECK_FILE="$ICHECK_DIR/icheck-bootstrap.min.css"
if [[ ! -f "$ICHECK_FILE" ]]; then
    print_warning "Baixando icheck-bootstrap para assets do login"
    mkdir -p "$ICHECK_DIR"
    curl -fsSL -o "$ICHECK_FILE" "https://cdn.jsdelivr.net/npm/icheck-bootstrap@3.0.1/icheck-bootstrap.min.css"
    chown -R www-data:www-data "$ICHECK_DIR"
fi

# Copiar logo customizado do projeto para um caminho estavel
CUSTOM_LOGO_SRC="$SOURCE_DIR/logo.png"
CUSTOM_LOGO_DST="$WORK_DIR/public/img/logo.png"
if [[ -f "$CUSTOM_LOGO_SRC" ]]; then
    mkdir -p "$WORK_DIR/public/img"
    cp -f "$CUSTOM_LOGO_SRC" "$CUSTOM_LOGO_DST"
    chown www-data:www-data "$CUSTOM_LOGO_DST"
else
    print_warning "Logo personalizado nao encontrado em $CUSTOM_LOGO_SRC"
fi

print_message "======================================"
print_message "Etapa 6: Configuracao do Nginx"
print_message "======================================"

cat > "/etc/nginx/sites-available/pmed2" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN;
    root $WORK_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:$PHP_FPM_SOCK;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
EOF

ln -sf /etc/nginx/sites-available/pmed2 /etc/nginx/sites-enabled/pmed2
rm -f /etc/nginx/sites-enabled/default

nginx -t
systemctl restart nginx
systemctl enable nginx

print_success "Nginx configurado"

print_message "======================================"
print_message "Etapa 7: Configuracao do Servico Systemd"
print_message "======================================"

if ! sudo -u www-data test -x "$WORK_DIR"; then
    print_error "www-data nao consegue acessar $WORK_DIR"
    print_error "Use --app-dir em /var/www ou ajuste permissoes/ACL"
    exit 1
fi

cat > /etc/systemd/system/pmed2.service << EOF
[Unit]
Description=PMED2 - Sistema de Gestao de Faturas Hospitalares
After=network.target mysql.service nginx.service $PHP_FPM_SERVICE.service
Requires=mysql.service nginx.service $PHP_FPM_SERVICE.service

[Service]
User=www-data
Group=www-data
Type=simple
WorkingDirectory=$WORK_DIR
ExecStart=/usr/bin/php $WORK_DIR/artisan queue:work --sleep=3 --tries=3 --timeout=90
Restart=always
RestartSec=3

Environment="APP_ENV=production"
Environment="APP_DEBUG=false"

StandardOutput=append:/var/log/pmed2.log
StandardError=append:/var/log/pmed2-error.log
SyslogIdentifier=pmed2

NoNewPrivileges=true
PrivateTmp=true

[Install]
WantedBy=multi-user.target
EOF

: > /var/log/pmed2.log
: > /var/log/pmed2-error.log
chown www-data:www-data /var/log/pmed2.log /var/log/pmed2-error.log

print_message "Configurando aplicacao para modo producao..."
APP_URL="http://$DOMAIN"
if [[ "$USE_HTTPS" -eq 1 ]]; then
    APP_URL="https://$DOMAIN"
fi
if [[ -n "$APP_URL_OVERRIDE" ]]; then
    APP_URL="$APP_URL_OVERRIDE"
fi
APP_URL_SED=$(escape_sed_replacement "$APP_URL")
sed -i "s/APP_ENV=.*/APP_ENV=production/" "$WORK_DIR/.env"
sed -i "s/APP_DEBUG=.*/APP_DEBUG=false/" "$WORK_DIR/.env"
sed -i "s#APP_URL=.*#APP_URL=$APP_URL_SED#" "$WORK_DIR/.env"

systemctl daemon-reload
systemctl enable pmed2.service
systemctl restart pmed2.service

sleep 2
if systemctl is-active --quiet pmed2.service; then
    print_success "Servico PMED2 iniciado com sucesso"
else
    print_error "Erro ao iniciar servico PMED2"
    journalctl -u pmed2.service -n 50 --no-pager
fi

print_message "======================================"
print_message "Etapa 8: Otimizacoes"
print_message "======================================"

( cd "$WORK_DIR" && php artisan config:cache )
( cd "$WORK_DIR" && php artisan route:cache )
( cd "$WORK_DIR" && php artisan view:cache )

print_success "Cache otimizado"

print_message "======================================"
print_message "✓ Instalacao Concluida!"
print_message "======================================"

print_success "Sistema PMED2 instalado com sucesso!"
print_message "Informacoes de acesso:"
print_message "  URL: $APP_URL"
print_message "  Banco: $DB_NAME"
print_message "  Usuario DB: $DB_USER"
print_message "Gerenciamento do servico:"
print_message "  Iniciar:   sudo systemctl start pmed2"
print_message "  Parar:     sudo systemctl stop pmed2"
print_message "  Reiniciar: sudo systemctl restart pmed2"
print_message "  Status:    sudo systemctl status pmed2"
print_message "  Logs:      sudo journalctl -u pmed2 -f"

print_warning "IMPORTANTE:"
print_message "  1. Configure um usuario administrador no banco de dados"
print_message "  2. Revise as configuracoes em .env"
print_message "  3. Configure SSL/HTTPS para producao (certbot)"
print_message "  4. Configure backup automatico do banco de dados"
print_message "  5. O servico inicia automaticamente no boot do sistema"

print_message "Logs do sistema:"
print_message "  Aplicacao: tail -f /var/log/pmed2.log"
print_message "  Erros: tail -f /var/log/pmed2-error.log"
print_message "  Laravel: tail -f $WORK_DIR/storage/logs/laravel.log"

exit 0
