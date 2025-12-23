#!/bin/bash

################################################################################
# PMED2 - Sistema de Gestão de Faturas Hospitalares
# Script de Instalação Automatizada
# 
# Autor: Felipe Pedrosa
# Email: xlipesousa@gmail.com
# Data: 23/12/2025
# 
# Descrição: Script para instalação completa do sistema PMED2
#            Baseado em requirements.txt
################################################################################

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir mensagens
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

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then 
    print_error "Este script precisa ser executado como root (use sudo)"
    exit 1
fi

print_message "======================================"
print_message "PMED2 - Instalação do Sistema"
print_message "======================================"
echo ""

# Detectar distribuição
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$NAME
    VER=$VERSION_ID
else
    print_error "Não foi possível detectar a distribuição Linux"
    exit 1
fi

print_message "Sistema detectado: $OS $VER"

# Verificar se é Ubuntu/Debian
if [[ "$OS" != *"Ubuntu"* ]] && [[ "$OS" != *"Debian"* ]]; then
    print_warning "Este script foi testado apenas em Ubuntu/Debian"
    read -p "Deseja continuar? (s/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Ss]$ ]]; then
        exit 1
    fi
fi

echo ""
print_message "======================================"
print_message "Etapa 1: Atualização do Sistema"
print_message "======================================"
echo ""

apt update
apt upgrade -y

print_success "Sistema atualizado"

echo ""
print_message "======================================"
print_message "Etapa 2: Instalação de Dependências"
print_message "======================================"
echo ""

# Adicionar repositório PHP 8.2
print_message "Adicionando repositório PHP 8.2..."
apt install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt update

# Instalar pacotes base
print_message "Instalando pacotes base do sistema..."
apt install -y \
    nginx \
    git \
    curl \
    wget \
    unzip \
    zip \
    supervisor

print_success "Pacotes base instalados"

# Instalar PHP 8.2 e extensões
print_message "Instalando PHP 8.2 e extensões..."
apt install -y \
    php8.2 \
    php8.2-fpm \
    php8.2-cli \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-bcmath \
    php8.2-curl \
    php8.2-gd \
    php8.2-zip \
    php8.2-intl

print_success "PHP 8.2 instalado"

# Verificar instalação do PHP
php -v

# Instalar MySQL
print_message "Instalando MySQL Server..."
apt install -y mysql-server mysql-client

print_success "MySQL instalado"

# Instalar Node.js e NPM
print_message "Instalando Node.js 18.x..."
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

print_success "Node.js instalado"
node -v
npm -v

# Atualizar NPM
print_message "Atualizando NPM..."
npm install -g npm@latest

# Instalar Composer
print_message "Instalando Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
chmod +x /usr/local/bin/composer

print_success "Composer instalado"
composer --version

echo ""
print_message "======================================"
print_message "Etapa 3: Configuração do MySQL"
print_message "======================================"
echo ""

# Iniciar MySQL
systemctl start mysql
systemctl enable mysql

print_message "Configurando banco de dados..."
echo ""
echo "Por favor, informe os dados do banco de dados:"
read -p "Nome do banco de dados [pmed2]: " DB_NAME
DB_NAME=${DB_NAME:-pmed2}

read -p "Usuário do banco de dados [pmed2user]: " DB_USER
DB_USER=${DB_USER:-pmed2user}

read -sp "Senha do banco de dados: " DB_PASS
echo ""

# Criar banco de dados e usuário
mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

print_success "Banco de dados configurado"

echo ""
print_message "======================================"
print_message "Etapa 4: Configuração da Aplicação"
print_message "======================================"
echo ""

# Obter diretório atual
INSTALL_DIR=$(pwd)
print_message "Diretório de instalação: $INSTALL_DIR"

# Verificar se existe composer.json
if [ ! -f "$INSTALL_DIR/composer.json" ]; then
    print_error "Arquivo composer.json não encontrado!"
    print_error "Certifique-se de estar no diretório raiz do projeto"
    exit 1
fi

# Copiar .env.example para .env se não existir
if [ ! -f "$INSTALL_DIR/.env" ]; then
    if [ -f "$INSTALL_DIR/.env.example" ]; then
        print_message "Criando arquivo .env..."
        cp "$INSTALL_DIR/.env.example" "$INSTALL_DIR/.env"
        print_success "Arquivo .env criado"
    else
        print_error "Arquivo .env.example não encontrado!"
        exit 1
    fi
else
    print_warning "Arquivo .env já existe, mantendo configurações atuais"
fi

# Atualizar .env com dados do banco
print_message "Configurando .env com dados do banco..."
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" "$INSTALL_DIR/.env"
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" "$INSTALL_DIR/.env"
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" "$INSTALL_DIR/.env"

# Instalar dependências do Composer
print_message "Instalando dependências PHP (Composer)..."
composer install --no-interaction --prefer-dist --optimize-autoloader

print_success "Dependências PHP instaladas"

# Instalar dependências do NPM
print_message "Instalando dependências JavaScript (NPM)..."
npm install

print_success "Dependências JavaScript instaladas"

# Gerar chave da aplicação
print_message "Gerando chave da aplicação..."
php artisan key:generate --force

print_success "Chave da aplicação gerada"

# Executar migrations
print_message "Executando migrations do banco de dados..."
php artisan migrate --force

print_success "Migrations executadas"

# Criar link simbólico para storage
print_message "Criando link simbólico para storage..."
php artisan storage:link

print_success "Link de storage criado"

# Compilar assets
print_message "Compilando assets frontend..."
npm run build

print_success "Assets compilados"

echo ""
print_message "======================================"
print_message "Etapa 5: Configuração de Permissões"
print_message "======================================"
echo ""

# Definir permissões corretas
print_message "Configurando permissões de diretórios..."
chown -R www-data:www-data "$INSTALL_DIR"
chmod -R 755 "$INSTALL_DIR"
chmod -R 775 "$INSTALL_DIR/storage"
chmod -R 775 "$INSTALL_DIR/bootstrap/cache"

print_success "Permissões configuradas"

echo ""
print_message "======================================"
print_message "Etapa 6: Configuração do Nginx"
print_message "======================================"
echo ""

read -p "Configurar servidor web Nginx? (S/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Ss]$ ]] || [[ -z $REPLY ]]; then
    read -p "Nome do domínio ou IP [localhost]: " DOMAIN
    DOMAIN=${DOMAIN:-localhost}
    
    # Criar configuração do Nginx
    cat > "/etc/nginx/sites-available/pmed2" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN;
    root $INSTALL_DIR/public;

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
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

    # Habilitar site
    ln -sf /etc/nginx/sites-available/pmed2 /etc/nginx/sites-enabled/pmed2
    
    # Remover site padrão
    rm -f /etc/nginx/sites-enabled/default
    
    # Testar configuração
    nginx -t
    
    # Reiniciar Nginx
    systemctl restart nginx
    systemctl enable nginx
    
    print_success "Nginx configurado"
fi

echo ""
print_message "======================================"
print_message "Etapa 7: Otimizações"
print_message "======================================"
echo ""

# Cache de configuração
print_message "Otimizando cache da aplicação..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

print_success "Cache otimizado"

echo ""
print_message "======================================"
print_message "✓ Instalação Concluída!"
print_message "======================================"
echo ""

print_success "Sistema PMED2 instalado com sucesso!"
echo ""
print_message "Informações de acesso:"
echo -e "  ${GREEN}URL:${NC} http://$DOMAIN"
echo -e "  ${GREEN}Banco:${NC} $DB_NAME"
echo -e "  ${GREEN}Usuário DB:${NC} $DB_USER"
echo ""
print_warning "IMPORTANTE:"
echo "  1. Configure um usuário administrador no banco de dados"
echo "  2. Revise as configurações em .env"
echo "  3. Configure SSL/HTTPS para produção"
echo "  4. Configure backup automático do banco de dados"
echo ""
print_message "Para iniciar em modo desenvolvimento:"
echo "  php artisan serve"
echo "  npm run dev"
echo ""
print_message "Logs do sistema:"
echo "  tail -f storage/logs/laravel.log"
echo ""

exit 0
