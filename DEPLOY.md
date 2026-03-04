# 📋 Guia de Deploy - Documentação PMED2

## 🎯 Situação Atual

Você está em **ambiente de desenvolvimento** com:
- ✅ 3 commits locais à frente (funcionalidades de pagamento/anulação)
- ✅ Documentação nova no GitHub (README.md, relatorio.md, install.sh, etc.)
- ⚠️ Arquivos locais precisam ser mesclados com documentação do GitHub

## 🧭 Governança de Atualização

- A atualização da aplicação PMED2 é **exclusivamente** pelo fluxo oficial de CI/CD.
- O mecanismo de upgrade via painel web (`/configuracoes/upgrade`) foi descontinuado.
- Scripts locais (`install.sh`, `update.sh`) permanecem para cenários operacionais de infraestrutura, fora da interface administrativa.

## 🚀 Opções de Deploy

### Opção 1: Script Automático (RECOMENDADO)

O script `deploy-docs.sh` faz tudo automaticamente com segurança:

```bash
# No servidor (DEV ou PRODUÇÃO)
cd /home/admin21ct/pmed2
./deploy-docs.sh
```

**O que o script faz:**
1. ✅ Cria backup automático
2. ✅ Salva suas mudanças locais (stash)
3. ✅ Busca atualizações do GitHub
4. ✅ Aplica APENAS arquivos de documentação:
   - README.md
   - README.laravel.md
   - relatorio.md
   - requirements.txt
   - install.sh
   - update.sh
   - .gitignore
   - .env.example
5. ✅ Torna scripts executáveis
6. ✅ Oferece recuperar suas mudanças locais
7. ✅ Rollback automático em caso de erro

**Vantagens:**
- Não toca no código funcional
- Backup automático
- Recuperação automática de erros
- Interativo e seguro

---

### Opção 2: Manual Controlado

Se preferir fazer manualmente:

```bash
# 1. Criar backup
cd /home/admin21ct/pmed2
mkdir -p ../backup-docs-$(date +%Y%m%d)
cp -f README.md .gitignore .env.example ../backup-docs-$(date +%Y%m%d)/ 2>/dev/null || true

# 2. Salvar mudanças locais
git stash push -u -m "Backup antes do deploy docs"

# 3. Buscar atualizações
git fetch origin

# 4. Aplicar APENAS os arquivos de documentação
git checkout origin/main -- README.md
git checkout origin/main -- README.laravel.md
git checkout origin/main -- relatorio.md
git checkout origin/main -- requirements.txt
git checkout origin/main -- install.sh
git checkout origin/main -- update.sh
git checkout origin/main -- .gitignore
git checkout origin/main -- .env.example

# 5. Tornar scripts executáveis
chmod +x install.sh update.sh

# 6. Recuperar suas mudanças locais
git stash pop

# Se houver conflitos, resolva e:
# git add .
# git stash drop
```

---

### Opção 3: Merge Completo (Para Produção Final)

Quando quiser sincronizar completamente DEV com GitHub:

```bash
# 1. Backup completo
cd /home/admin21ct/pmed2
tar -czf ../backup-pmed2-$(date +%Y%m%d-%H%M%S).tar.gz .

# 2. Commit suas mudanças locais
git add -A
git commit -m "feat: Implementação de pagamento e anulação"

# 3. Merge com origin/main
git pull origin main --no-rebase

# 4. Resolver conflitos se houver
# Edite os arquivos marcados com conflito
# Depois:
git add .
git commit -m "merge: Mescla com documentação do GitHub"

# 5. Push tudo para o GitHub
git push origin main
```

---

## 📦 Para Produção (Servidor Remoto)

### Cenário: Aplicar documentação em servidor de produção

```bash
# 1. Conectar via SSH ao servidor de produção
ssh usuario@servidor-producao

# 2. Ir para o diretório do projeto
cd /var/www/pmed2  # ou caminho do seu projeto

# 3. Criar backup
sudo tar -czf /backup/pmed2-$(date +%Y%m%d-%H%M%S).tar.gz .

# 4. Copiar o script de deploy
# (você pode copiar o arquivo deploy-docs.sh via SCP/FTP)

# OU fazer manualmente:
git stash
git fetch origin
git checkout origin/main -- README.md relatorio.md requirements.txt install.sh update.sh .gitignore .env.example
chmod +x install.sh update.sh
git stash pop
```

---

## 🔍 Verificação Pós-Deploy

Após executar qualquer opção, verifique:

```bash
# 1. Ver arquivos alterados
git status

# 2. Ver diferenças
git diff

# 3. Listar novos arquivos de documentação
ls -lh README.md relatorio.md requirements.txt install.sh update.sh

# 4. Testar scripts (sem executar)
./install.sh --help
./update.sh --help
```

---

## ⚠️ Resolução de Conflitos

Se aparecer conflitos após `git stash pop`:

```bash
# 1. Ver arquivos com conflito
git status

# 2. Editar manualmente cada arquivo
# Procure por marcadores: <<<<<<<, =======, >>>>>>>

# 3. Escolher qual versão manter ou mesclar manualmente

# 4. Marcar como resolvido
git add <arquivo-resolvido>

# 5. Finalizar
git stash drop
```

---

## 🎓 Exemplos de Uso

### Exemplo 1: Deploy em DEV (seu caso atual)

```bash
cd /home/admin21ct/pmed2
./deploy-docs.sh
# Seguir as instruções interativas
# Escolher recuperar mudanças locais quando perguntado
```

### Exemplo 2: Deploy em Produção sem afetar código

```bash
# No servidor de produção
cd /var/www/pmed2
./deploy-docs.sh
# NÃO recuperar mudanças locais (se não houver)
```

### Exemplo 3: Sincronização completa DEV → GitHub → Produção

```bash
# No DEV:
git add -A
git commit -m "feat: Todas as funcionalidades finalizadas"
git push origin main

# No Produção:
git pull origin main
php artisan migrate
php artisan optimize:clear
```

---

## 🛡️ Segurança e Rollback

### Política de artefatos legados de upgrade

Com a descontinuação do upgrade via painel web, os artefatos abaixo passam a ser tratados como legado operacional:

- `storage/logs/upgrade.log`
- `storage/logs/upgrade-status.json`

Diretriz:
- Não são mais fonte de verdade para deploy;
- Podem ser arquivados para histórico local quando existirem;
- Podem ser removidos em rotina de limpeza, desde que não haja investigação em curso.

Exemplo de limpeza opcional:

```bash
rm -f storage/logs/upgrade.log storage/logs/upgrade-status.json
```

### Se algo der errado:

```bash
# Opção 1: Restaurar do backup automático
cp -rf ../backup-pmed2-YYYYMMDD-HHMMSS/* ./

# Opção 2: Desfazer com Git
git reset --hard HEAD
git stash pop  # se tiver mudanças salvas

# Opção 3: Rollback completo (último recurso)
git reset --hard origin/main
```

---

## 📞 Suporte

**Arquivos importantes criados:**
- `deploy-docs.sh` - Script de deploy seguro
- `install.sh` - Instalação completa do sistema
- `update.sh` - Atualização segura do sistema
- `relatorio.md` - Documentação técnica completa
- `requirements.txt` - Lista de dependências

**Próximos passos após deploy:**
1. ✅ Revisar documentação em `relatorio.md`
2. ✅ Testar scripts de instalação/atualização (em teste)
3. ✅ Aplicar em produção quando validado

---

## 🎯 Recomendação Final

**Para seu caso (DEV com 3 commits locais):**

```bash
# Execute o script automático
./deploy-docs.sh

# Quando perguntado "Deseja recuperar suas mudanças locais?":
# → Digite S (Sim)

# Se houver conflitos nos arquivos:
# → .gitignore: mantenha as exclusões (sua versão local provavelmente está melhor)
# → .env.example: mantenha a versão do GitHub (já sanitizada)
# → README.md: mantenha a versão do GitHub (documentação completa)
```

Depois de tudo ok em DEV, você pode:
```bash
git add -A
git commit -m "docs: Adiciona documentação completa do sistema"
git push origin main
```

E no servidor de produção:
```bash
git pull origin main
```

---

**Criado em:** 23/12/2025  
**Versão:** 1.0  
**Autor:** GitHub Copilot
