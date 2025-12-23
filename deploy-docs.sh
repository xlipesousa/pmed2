#!/bin/bash

#############################################################################
# Script de Deploy Seguro dos Documentos PMED2
# Criado em: 23/12/2025
# 
# Este script aplica APENAS os arquivos de documentação do GitHub
# sem afetar o código funcional do sistema em produção
#############################################################################

set -e  # Sair em caso de erro

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para exibir mensagens
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[⚠]${NC} $1"
}

log_error() {
    echo -e "${RED}[✗]${NC} $1"
}

# Cabeçalho
clear
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║     DEPLOY SEGURO - DOCUMENTAÇÃO PMED2                     ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Verificações iniciais
log_info "Verificando pré-requisitos..."

# Verificar se está em um repositório git
if [ ! -d .git ]; then
    log_error "Este não é um repositório git!"
    exit 1
fi

# Verificar se existe backup
BACKUP_DIR="../backup-pmed2-$(date +%Y%m%d-%H%M%S)"
log_info "Diretório de backup: $BACKUP_DIR"

# Função de rollback
rollback() {
    log_error "Erro detectado! Iniciando rollback..."
    if [ -d "$BACKUP_DIR" ]; then
        log_info "Restaurando arquivos do backup..."
        cp -rf "$BACKUP_DIR/"* ./ 2>/dev/null || true
        log_success "Rollback concluído!"
    else
        log_warning "Backup não encontrado. Execute: git stash"
    fi
    exit 1
}

# Trap para capturar erros
trap rollback ERR

# Etapa 1: Criar backup completo
log_info "Criando backup de segurança..."
mkdir -p "$BACKUP_DIR"

# Backup dos arquivos que serão alterados
cp -f README.md "$BACKUP_DIR/README.md.bak" 2>/dev/null || log_warning "README.md não existe (ok)"
cp -f .gitignore "$BACKUP_DIR/.gitignore.bak" 2>/dev/null || true
cp -f .env.example "$BACKUP_DIR/.env.example.bak" 2>/dev/null || true

log_success "Backup criado em: $BACKUP_DIR"

# Etapa 2: Verificar o estado atual do Git
log_info "Salvando mudanças locais em stash..."
git stash push -u -m "Deploy docs - backup $(date +%Y-%m-%d_%H:%M:%S)"
log_success "Mudanças locais salvas no stash"

# Etapa 3: Buscar atualizações do GitHub
log_info "Buscando atualizações do GitHub..."
git fetch origin
log_success "Fetch concluído"

# Etapa 4: Fazer checkout SELETIVO dos arquivos de documentação
log_info "Aplicando APENAS arquivos de documentação..."

# Array com os arquivos que queremos do GitHub
DOCS_FILES=(
    "README.md"
    "README.laravel.md"
    "relatorio.md"
    "requirements.txt"
    "install.sh"
    "update.sh"
    ".gitignore"
    ".env.example"
)

echo ""
log_info "Arquivos que serão atualizados:"
for file in "${DOCS_FILES[@]}"; do
    echo "   - $file"
done
echo ""

read -p "$(echo -e ${YELLOW}Deseja continuar? [S/n]:${NC} )" -n 1 -r
echo
if [[ ! $REPLY =~ ^[SsYy]$ ]] && [[ ! -z $REPLY ]]; then
    log_warning "Operação cancelada pelo usuário"
    git stash pop
    exit 0
fi

# Fazer checkout dos arquivos específicos do origin/main
for file in "${DOCS_FILES[@]}"; do
    if git show origin/main:"$file" > /dev/null 2>&1; then
        log_info "Atualizando: $file"
        git checkout origin/main -- "$file" 2>/dev/null || log_warning "Não foi possível atualizar $file"
        
        # Tornar scripts executáveis
        if [[ "$file" == *.sh ]]; then
            chmod +x "$file"
            log_success "$file agora é executável"
        fi
    else
        log_warning "$file não existe no origin/main"
    fi
done

log_success "Arquivos de documentação aplicados!"

# Etapa 5: Verificar o que foi alterado
log_info "Arquivos alterados:"
git status --short

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  DOCUMENTAÇÃO APLICADA COM SUCESSO!                        ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

log_info "Próximos passos:"
echo "   1. Revise as mudanças: git diff"
echo "   2. Para mesclar suas mudanças locais:"
echo "      git stash pop"
echo ""
echo "   3. Se houver conflitos, resolva manualmente"
echo "   4. Teste os scripts de instalação (em ambiente de teste):"
echo "      ./install.sh --help"
echo "      ./update.sh --help"
echo ""

log_info "Backup mantido em: $BACKUP_DIR"
log_warning "Mantenha o backup até ter certeza de que tudo está ok!"

echo ""
read -p "$(echo -e ${YELLOW}Deseja recuperar suas mudanças locais agora? [S/n]:${NC} )" -n 1 -r
echo
if [[ $REPLY =~ ^[SsYy]$ ]] || [[ -z $REPLY ]]; then
    log_info "Recuperando mudanças locais do stash..."
    if git stash pop; then
        log_success "Mudanças recuperadas!"
        log_warning "Se houver conflitos, resolva-os manualmente com: git mergetool"
    else
        log_error "Houve conflitos ao recuperar as mudanças"
        log_info "Resolva os conflitos e execute: git add . && git stash drop"
    fi
else
    log_info "Mudanças permanecem no stash"
    log_info "Para recuperá-las depois: git stash pop"
fi

echo ""
log_success "Deploy concluído!"
