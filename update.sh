#!/bin/bash

################################################################################
# PMED2 - Sistema de Gestão de Faturas Hospitalares
# Script de Atualização do Sistema
# 
# Autor: Felipe Pedrosa
# Email: xlipesousa@gmail.com
# Data: 23/12/2025
# 
# Descrição: Script para atualizar o sistema PMED2 a partir do repositório Git
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

print_message "======================================"
print_message "PMED2 - Atualização do Sistema"
print_message "======================================"
echo ""

# Verificar se estamos em um repositório git
if [ ! -d ".git" ]; then
    print_error "Este diretório não é um repositório Git!"
    print_error "Execute este script no diretório raiz do projeto PMED2"
    exit 1
fi

# Verificar se há alterações não commitadas
if ! git diff-index --quiet HEAD --; then
    print_warning "Existem alterações não commitadas no repositório"
    echo ""
    git status --short
    echo ""
    read -p "Deseja fazer backup das alterações e continuar? (s/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        # Criar backup das alterações
        BACKUP_DIR="backups/$(date +%Y%m%d_%H%M%S)"
        mkdir -p "$BACKUP_DIR"
        
        print_message "Criando backup em $BACKUP_DIR..."
        git diff HEAD > "$BACKUP_DIR/changes.patch"
        cp .env "$BACKUP_DIR/.env.backup" 2>/dev/null || true
        
        print_success "Backup criado"
        
        # Fazer stash das alterações
        print_message "Guardando alterações temporariamente..."
        git stash push -u -m "Auto-stash antes de update $(date +%Y-%m-%d\ %H:%M:%S)"
        print_success "Alterações guardadas"
    else
        print_error "Atualização cancelada pelo usuário"
        exit 1
    fi
fi

echo ""
print_message "======================================"
print_message "Etapa 1: Ativando Modo Manutenção"
print_message "======================================"
echo ""

# Colocar aplicação em modo manutenção
php artisan down --retry=60
print_success "Aplicação em modo manutenção"

echo ""
print_message "======================================"
print_message "Etapa 2: Atualizando Código Fonte"
print_message "======================================"
echo ""

# Buscar atualizações do repositório
print_message "Buscando atualizações do repositório..."
git fetch origin

# Mostrar o que será atualizado
print_message "Mudanças a serem aplicadas:"
git log HEAD..origin/main --oneline --graph --decorate || git log HEAD..origin/master --oneline --graph --decorate

echo ""
read -p "Deseja continuar com a atualização? (S/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Nn]$ ]]; then
    print_warning "Atualização cancelada pelo usuário"
    php artisan up
    exit 0
fi

# Pull das alterações
print_message "Aplicando atualizações..."
if git pull origin main 2>/dev/null || git pull origin master 2>/dev/null; then
    print_success "Código atualizado"
else
    print_error "Erro ao atualizar o código"
    php artisan up
    exit 1
fi

echo ""
print_message "======================================"
print_message "Etapa 3: Atualizando Dependências"
print_message "======================================"
echo ""

# Atualizar dependências do Composer
print_message "Atualizando dependências PHP (Composer)..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
print_success "Dependências PHP atualizadas"

# Atualizar dependências do NPM
print_message "Atualizando dependências JavaScript (NPM)..."
npm install --production
print_success "Dependências JavaScript atualizadas"

echo ""
print_message "======================================"
print_message "Etapa 4: Aplicando Migrations"
print_message "======================================"
echo ""

# Executar migrations
print_message "Verificando e aplicando migrations do banco de dados..."
php artisan migrate --force
print_success "Migrations aplicadas"

echo ""
print_message "======================================"
print_message "Etapa 5: Compilando Assets"
print_message "======================================"
echo ""

# Compilar assets
print_message "Compilando assets frontend..."
npm run build
print_success "Assets compilados"

echo ""
print_message "======================================"
print_message "Etapa 6: Limpando e Otimizando Cache"
print_message "======================================"
echo ""

# Limpar caches
print_message "Limpando caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Otimizar
print_message "Otimizando aplicação..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

print_success "Cache otimizado"

echo ""
print_message "======================================"
print_message "Etapa 7: Verificando Permissões"
print_message "======================================"
echo ""

# Verificar permissões
print_message "Verificando permissões de diretórios..."
chmod -R 775 storage bootstrap/cache
print_success "Permissões verificadas"

echo ""
print_message "======================================"
print_message "Etapa 8: Desativando Modo Manutenção"
print_message "======================================"
echo ""

# Tirar aplicação do modo manutenção
php artisan up
print_success "Aplicação disponível novamente"

echo ""
print_message "======================================"
print_message "✓ Atualização Concluída!"
print_message "======================================"
echo ""

print_success "Sistema PMED2 atualizado com sucesso!"
echo ""
print_message "Informações da atualização:"
git log -1 --pretty=format:"  Commit: %h%n  Autor: %an <%ae>%n  Data: %ad%n  Mensagem: %s%n"
echo ""

# Verificar se há stash para restaurar
STASH_COUNT=$(git stash list | wc -l)
if [ $STASH_COUNT -gt 0 ]; then
    echo ""
    print_warning "Você tem alterações guardadas em stash"
    print_message "Para restaurá-las, execute:"
    echo "  git stash pop"
    echo ""
fi

print_message "Recomendações pós-atualização:"
echo "  1. Verificar os logs: tail -f storage/logs/laravel.log"
echo "  2. Testar funcionalidades críticas"
echo "  3. Verificar arquivo .env para novas configurações"
echo "  4. Revisar backups em: ./backups/"
echo ""

exit 0
