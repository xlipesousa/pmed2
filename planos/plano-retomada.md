# Plano de Retomada — PMED2

**Data de criação:** 2026-04-27  
**Versão do app:** 2.1.4  
**Branch ativa:** `refactor/remove-upgrade-web-phase1`  
**Ponto de restauração (snapshot VM):** `2026-04-27 — antes-retomada`

---

## 1. Contexto e Diagnóstico

### 1.1 Situação anterior declarada

O projeto foi interrompido durante a fase de migração de arquivos locais para containers. O estado exato da interrupção era desconhecido.

### 1.2 Resultado do diagnóstico técnico (2026-04-27)

**Conclusão principal:** a migração para containers **não está no zero**. O pipeline CI/CD containerizado foi implementado e está estruturado. O projeto está em estado **híbrido**: infra de entrega containerizada coexiste com scripts legados de deploy no host.

**Fase real de interrupção:**  
Pós-implantação da esteira CI/CD e containerização, durante a fase de **convergência e higienização operacional** da VM de desenvolvimento. A VM ficou com estado Git contaminado (vendor/node_modules/storage tracked no Git) e fluxos legados de host não foram desativados formalmente.

### 1.3 Problemas críticos identificados

| # | Problema | Impacto | Prioridade |
|---|----------|---------|-----------|
| P1 | `vendor/` e `node_modules/` rastreados pelo Git (committed) apesar de estarem no `.gitignore` | 860+ arquivos de ruído em `git status`; CI pode instalar versões erradas | CRÍTICO |
| P2 | `.env` rastreado pelo Git | Risco de vazar credenciais no repositório | CRÍTICO |
| P3 | `storage/framework/views/` rastreado com arquivos compilados | Ruído e inconsistência entre ambientes | ALTO |
| P4 | `main` local atrasada em relação a `origin/main` | Trabalho feito na branch errada ou sync perdido | ALTO |
| P5 | Scripts legados de host coexistindo com containers sem guardrails | Risco de deploys conflitantes em homolog/prod | MÉDIO |
| P6 | Workflows de CD usam `self-hosted` runner (`pmed2-interno`) sem confirmação de disponibilidade | Pipeline de CD pode estar inoperante | MÉDIO |
| P7 | `relatorio.md` e `plano-ci-cd.md` deletados na working tree (não commitados) | Perda de histórico se limpeza for feita sem cautela | BAIXO |

### 1.4 Estado do repositório Git

```
Remote: origin → https://github.com/xlipesousa/pmed2.git

Branches locais:
  refactor/remove-upgrade-web-phase1  69f2d4a  [origin = synced]  ← HEAD
  main                                66dc36e  [origin/main: behind 17]
  chore/deps-security-audit-fixes     2dcd01a  [origin = synced]

Tags (mais recentes):
  v2.1.4 — fix(console): corrigir comandos artisan com kernel custom
  v2.1.3 — Avaliação de desempenho - release
  v2.1.2 — Avaliação de desempenho - início implementação
  v2.0.0 — versionamento semântico 2.0.0 e validação CI/CD

Working tree:
  932 arquivos modificados (848 vendor, 13 node_modules, ~71 reais)
  2 arquivos deletados (plano-ci-cd.md, relatorio.md) — não commitados
  Stash: vazio
```

### 1.5 Arquitetura de entrega atual

```
Dev (VM local)
   │
   └─ git push / PR ──► GitHub (xlipesousa/pmed2)
                              │
                    ┌─────────┼─────────┐
                    │         │         │
                  ci.yml  docker-build  cd-homolog.yml (tag v*.*.*)
                    │         │         │
               quality    GHCR push   self-hosted runner (pmed2-interno)
               package         │         │
                         ghcr.io/        SSH → servidor homolog
                         xlipesousa/     docker compose up
                         pmed2           rollback automático
                                         │
                              (aprovação manual environment: homolog)
                                         │
                                   cd-prod.yml (workflow_dispatch)
                                         │
                              self-hosted runner (pmed2-interno)
                                         │
                               SSH → servidor produção
                               docker compose up
                               rollback manual via input
```

---

## 2. Ponto de Restauração

| Campo | Valor |
|-------|-------|
| Snapshot VM | `antes-retomada` (2026-04-27) |
| Branch | `refactor/remove-upgrade-web-phase1` |
| Commit HEAD | `69f2d4a` |
| Tag mais recente | `v2.1.4` |
| Estado da working tree | 932 arquivos modificados (ruído de dependências) |

**Para restaurar ao estado anterior ao plano:**
```bash
# Via hypervisor: reverter snapshot "antes-retomada"
# Ou via Git (estado do código apenas):
git checkout refactor/remove-upgrade-web-phase1
git reset --hard 69f2d4a
```

---

## 3. Plano de Retomada por Fases

---

### FASE 0 — Saneamento do Baseline Git
**Objetivo:** eliminar ruído da working tree, deixar `git status` limpo e confiável.  
**Bloqueante:** todas as fases seguintes.  
**Risco:** baixo — não altera código funcional, apenas tracking do Git.  
**Estimativa:** ~30 min.

#### 0.1 Registrar estado atual antes de qualquer mudança

```bash
cd /home/admin21ct/pmed2

# Criar artefato de auditoria do estado atual
git status --porcelain > /tmp/pmed2-status-antes-saneamento.txt
git diff --stat > /tmp/pmed2-diff-antes-saneamento.txt
echo "Arquivos rastreados que deveriam ser ignorados:" > /tmp/pmed2-tracked-ignored.txt
git ls-files vendor node_modules storage/framework .env | head -50 >> /tmp/pmed2-tracked-ignored.txt
echo "Gerado em: $(date)" >> /tmp/pmed2-tracked-ignored.txt

echo "Artefatos salvos em /tmp/pmed2-*"
```

#### 0.2 Remover tracking de vendor, node_modules, storage compilado e .env

```bash
cd /home/admin21ct/pmed2

# ATENÇÃO: git rm --cached NÃO apaga os arquivos do disco, apenas para de rastreá-los
# Confirmar antes de executar:
git ls-files vendor/ | wc -l        # deve mostrar ~800+
git ls-files node_modules/ | wc -l  # deve mostrar ~13+
git ls-files storage/framework/views/ | wc -l

# Remover tracking
git rm -r --cached vendor/
git rm -r --cached node_modules/
git rm -r --cached storage/framework/views/
git rm --cached .env 2>/dev/null || true

# Verificar: os arquivos ainda devem existir no disco
ls vendor/autoload.php
ls node_modules/.package-lock.json
ls .env
```

#### 0.3 Commit do saneamento

```bash
cd /home/admin21ct/pmed2

git status --short    # deve mostrar apenas D (deleted from index)
git diff --cached --stat  # confirmar o que será commitado

git add .gitignore  # garantir que .gitignore está atualizado

git commit -m "chore(git): remover vendor/node_modules/.env do tracking do Git

Esses diretórios estavam comprometidos no repositório em versão anterior
ao .gitignore correto. O .gitignore já os declara como ignorados.
Foram removidos do index sem deletar os arquivos do disco.

Arquivos afetados:
- vendor/ (~850 arquivos)
- node_modules/ (~13 arquivos)
- storage/framework/views/ (3 arquivos compilados)
- .env (credenciais não devem ser versionadas)"
```

#### 0.4 Verificar estado pós-saneamento

```bash
cd /home/admin21ct/pmed2

git status --short    # deve mostrar somente arquivos reais modificados
git status --short | wc -l  # deve ser < 15

# Comparar com snapshot de antes
diff /tmp/pmed2-status-antes-saneamento.txt <(git status --porcelain) | head -20
```

#### 0.5 Sincronizar branch main local

```bash
cd /home/admin21ct/pmed2

# main local está 17 commits atrás de origin/main
git checkout main
git pull origin main
git checkout refactor/remove-upgrade-web-phase1

# Confirmar que origin/main == HEAD da branch atual (69f2d4a)
git log --oneline -3 main
git log --oneline -3 origin/main
```

#### 0.6 Push do saneamento

```bash
cd /home/admin21ct/pmed2

git push origin refactor/remove-upgrade-web-phase1

# Verificar status final
git status -sb
```

**Critério de saída (DoD) Fase 0:**
- [ ] `git status --short` mostra menos de 15 arquivos
- [ ] `vendor/`, `node_modules/`, `.env` não aparecem no `git status`
- [ ] `main` local sincronizada com `origin/main`
- [ ] Commit de saneamento no log

---

### FASE 1 — Validação do Pipeline CI/CD
**Objetivo:** confirmar que a cadeia de entrega está operacional.  
**Depende de:** Fase 0 concluída.  
**Estimativa:** ~1h.

#### 1.1 Inspecionar segredos necessários no GitHub

Acessar: https://github.com/xlipesousa/pmed2/settings/secrets/actions

Segredos obrigatórios para CD:

| Segredo | Usado em | Descrição |
|---------|---------|-----------|
| `PMED2_HOM_SSH_KEY` | cd-homolog | Chave privada SSH para homolog |
| `PMED2_HOM_SSH_HOST` | cd-homolog | Hostname/IP do servidor de homolog |
| `PMED2_HOM_SSH_USER` | cd-homolog | Usuário SSH de homolog |
| `PMED2_HOM_DB_PASSWORD` | cd-homolog | Senha do banco em homolog |
| `PMED2_HOM_GHCR_USER` | cd-homolog | Usuário GHCR para pull em homolog |
| `PMED2_HOM_GHCR_TOKEN` | cd-homolog | Token GHCR para pull em homolog |
| `PMED2_PROD_SSH_KEY` | cd-prod | Chave privada SSH para produção |
| `PMED2_PROD_SSH_HOST` | cd-prod | Hostname/IP do servidor de produção |
| `PMED2_PROD_SSH_USER` | cd-prod | Usuário SSH de produção |
| `PMED2_PROD_DB_PASSWORD` | cd-prod | Senha do banco em produção |
| `PMED2_PROD_GHCR_USER` | cd-prod | Usuário GHCR para pull em produção |
| `PMED2_PROD_GHCR_TOKEN` | cd-prod | Token GHCR para pull em produção |

**Ação:** Listar quais estão configurados e quais estão faltando.

#### 1.2 Verificar runner self-hosted

```bash
# No servidor que hospeda o runner:
# Acessar https://github.com/xlipesousa/pmed2/settings/actions/runners
# Verificar se o runner "pmed2-interno" aparece como "Active/Idle"

# No host do runner (quando tiver acesso):
systemctl status actions.runner.* 2>/dev/null || \
  ps aux | grep -i runner | grep -v grep
```

#### 1.3 Validar CI disparando manualmente

```bash
cd /home/admin21ct/pmed2

# Opção A: criar um commit sem código e fazer push
echo "# CI check $(date +%Y%m%d)" >> /tmp/ci-test.txt
# NÃO fazer isso em main — usar branch de teste

# Opção B: via GitHub CLI (se disponível)
gh workflow run ci.yml --ref refactor/remove-upgrade-web-phase1 2>/dev/null || \
  echo "gh não disponível ou workflow não aceita dispatch — verificar na UI do GitHub"
```

#### 1.4 Validar build Docker localmente

```bash
cd /home/admin21ct/pmed2

# Verificar se Docker está disponível
docker version
docker buildx version

# Build de teste (sem push)
docker build -t pmed2-test:local . \
  --progress=plain 2>&1 | tail -30

# Resultado esperado: imagem criada com sucesso
docker images pmed2-test:local
```

#### 1.5 Verificar imagem publicada no GHCR

```bash
# Verificar último digest publicado
docker manifest inspect ghcr.io/xlipesousa/pmed2:main 2>/dev/null | \
  python3 -m json.tool | grep -E '"digest"|"created"' | head -5 || \
  echo "Verificar em: https://github.com/xlipesousa/pmed2/pkgs/container/pmed2"
```

**Critério de saída (DoD) Fase 1:**
- [ ] Segredos CD mapeados (existentes e faltantes documentados)
- [ ] Runner self-hosted confirmado como ativo ou problema documentado
- [ ] Build Docker local bem-sucedido
- [ ] CI passando na branch atual (evidência na UI do GitHub)

---

### FASE 2 — Validação da Stack de Containers Local
**Objetivo:** confirmar que a aplicação sobe e responde corretamente em containers na VM de dev.  
**Depende de:** Fase 0 concluída, Docker disponível.  
**Estimativa:** ~45 min.

#### 2.1 Preparar secrets locais

```bash
cd /home/admin21ct/pmed2

# Verificar o diretório de secrets
ls -la .secrets.example/
cat .secrets.example/db_password

# Criar secrets de dev (se não existirem)
mkdir -p .secrets
echo "dev_password_local" > .secrets/db_password
echo "dev_root_local" > .secrets/db_root_password
chmod 600 .secrets/db_password .secrets/db_root_password
```

#### 2.2 Preparar .env local

```bash
cd /home/admin21ct/pmed2

# Verificar .env atual
cat .env | grep -v PASSWORD | grep -v KEY | grep -v SECRET  # não exibir segredos

# Se não existir, criar a partir do exemplo
cp .env.example .env 2>/dev/null || true

# Confirmar variáveis críticas
grep -E 'APP_URL|APP_ENV|DB_DATABASE|CACHE_STORE|QUEUE_CONNECTION' .env
```

#### 2.3 Subir a stack de containers

```bash
cd /home/admin21ct/pmed2

# Parar instâncias anteriores (se existirem)
docker compose down --remove-orphans 2>/dev/null || true

# Build e subida
docker compose up -d --build

# Aguardar serviços ficarem saudáveis
sleep 10
docker compose ps
docker compose logs app --tail=50
```

#### 2.4 Validar healthchecks e conectividade

```bash
cd /home/admin21ct/pmed2

# Verificar status dos serviços
docker compose ps

# Testar acesso HTTP
curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/ && echo ""

# Verificar logs de erro
docker compose logs web --tail=20
docker compose logs app --tail=20

# Testar artisan dentro do container
docker compose exec app php artisan --version
docker compose exec app php artisan migrate:status 2>/dev/null | head -20 || true
```

#### 2.5 Validar queue e scheduler

```bash
cd /home/admin21ct/pmed2

# Verificar containers auxiliares
docker compose logs queue --tail=20
docker compose logs scheduler --tail=20

# Confirmar que não há erros de conexão com Redis/DB
docker compose exec queue php artisan queue:monitor 2>/dev/null || true
```

#### 2.6 Testar migração controlada

```bash
cd /home/admin21ct/pmed2

# ATENÇÃO: RUN_MIGRATIONS=false por padrão no docker-compose.yml local
# Migrar manualmente para controlar o processo

docker compose exec app php artisan migrate:status
docker compose exec app php artisan migrate --pretend 2>&1 | head -30

# Se OK, executar
docker compose exec app php artisan migrate --force
```

**Critério de saída (DoD) Fase 2:**
- [ ] `docker compose ps` mostra todos os serviços Up/healthy
- [ ] HTTP 200 em `http://localhost:8080/`
- [ ] `php artisan migrate:status` sem erros
- [ ] Logs de app, queue e scheduler sem errors fatais

---

### FASE 3 — Avaliação de Prontidão Operacional
**Objetivo:** mapa completo do que está e do que não está pronto para retomada de desenvolvimento.  
**Depende de:** Fases 0–2 concluídas.  
**Estimativa:** ~2h.

#### 3.1 Checklist de servidor de homologação

Verificar no servidor de homolog (`PMED2_HOM_SSH_HOST`):

```bash
# Conectar via SSH (substituir com dados reais do segredo)
ssh -i ~/.ssh/pmed2_homolog <PMED2_HOM_SSH_USER>@<PMED2_HOM_SSH_HOST>

# Verificar estrutura esperada pelo CD
ls -la /var/www/pmed2/deploy/
ls -la /var/www/pmed2/docker/nginx/
ls -la /var/www/pmed2/shared/.env 2>/dev/null || echo "FALTANDO: shared/.env"
ls -la /var/www/pmed2/shared/secrets/ 2>/dev/null || echo "FALTANDO: secrets"

# Verificar Docker
docker version
docker compose version

# Verificar se já há containers rodando
docker ps --filter name=pmed2

# Verificar último deploy (se houver)
cat /var/www/pmed2/shared/compose/current_tag 2>/dev/null || echo "Sem deploy anterior"
```

#### 3.2 Checklist de segurança

```bash
cd /home/admin21ct/pmed2

# Verificar se .env está no .gitignore corretamente
grep '\.env$\|^\.env' .gitignore

# Verificar se .secrets está no .gitignore
grep '\.secrets' .gitignore || echo "AVISO: .secrets pode não estar no .gitignore"

# Verificar permissões de secrets locais
ls -la .secrets/ 2>/dev/null

# Verificar se há credenciais hardcoded no código (excluindo vendor)
grep -rn --include="*.php" --include="*.env*" -E 'password\s*=\s*["\x27][^"\x27]+' \
  --exclude-dir=vendor --exclude-dir=node_modules \
  app/ config/ database/ routes/ 2>/dev/null | grep -v '\.example' | head -10
```

#### 3.3 Auditoria de dependências

```bash
cd /home/admin21ct/pmed2

# Verificar vulnerabilidades PHP
composer audit 2>&1 | tail -20

# Verificar vulnerabilidades NPM
npm audit --audit-level=high 2>&1 | tail -20

# Verificar versão PHP local
php --version

# Verificar se versão PHP local bate com a do Dockerfile
grep 'FROM php' Dockerfile
```

#### 3.4 Validar testes

```bash
cd /home/admin21ct/pmed2

# Executar suite de testes
php artisan config:clear 2>/dev/null || true
./vendor/bin/phpunit --testdox 2>&1 | tail -40

# Ou via container
docker compose exec app php artisan config:clear
docker compose exec app ./vendor/bin/phpunit --testdox 2>&1 | tail -40
```

#### 3.5 Validar consistência entre workflows e compose

```bash
# Comparar nome da imagem entre arquivos
echo "=== docker-build.yml ==="
grep -E 'IMAGE_NAME|image:' .github/workflows/docker-build.yml | head -5

echo "=== cd-homolog.yml ==="
grep -E 'IMAGE_NAME|image:' .github/workflows/cd-homolog.yml | head -5

echo "=== compose.homolog.yml ==="
grep -E 'IMAGE_NAME|image:' deploy/compose.homolog.yml | head -5

echo "=== compose.prod.yml ==="
grep -E 'IMAGE_NAME|image:' deploy/compose.prod.yml | head -5
```

**Critério de saída (DoD) Fase 3:**
- [ ] Checklist de homolog preenchido (itens faltantes documentados com ação corretiva)
- [ ] Segurança: nenhuma credencial no tracking do Git
- [ ] Testes passando (ou falhas documentadas com plano de correção)
- [ ] Consistência de imagem Docker validada
- [ ] Auditoria de dependências sem high/critical sem ação

---

### FASE 4 — Plano de Convergência e Retomada de Desenvolvimento
**Objetivo:** fechar o estado híbrido e habilitar fluxo de desenvolvimento contínuo.  
**Depende de:** Fase 3 concluída.  
**Estimativa:** ~1 sprint.

#### 4.1 Decidir destino dos scripts legados

| Script | Decisão recomendada | Ação |
|--------|---------------------|------|
| `install.sh` | Manter para referência, desativar em prod | Adicionar `DEPRECATED` no cabeçalho |
| `update.sh` | Manter para referência | Adicionar `DEPRECATED` no cabeçalho |
| `scripts/deploy.sh` | Manter como fallback documentado | Adicionar aviso de que é backup manual |
| `scripts/hardening_sudo_phpfpm.sh` | Inativo — PHP-FPM agora corre em container | Documentar como legado e mover para `legacy/` |
| `scripts/configure_backup_cron.sh` | Avaliar migração para scheduler do container | Criar issue |

#### 4.2 Merge de branch de trabalho em main

```bash
cd /home/admin21ct/pmed2

# Após conclusão da refatoração na branch
git checkout main
git pull origin main
git merge refactor/remove-upgrade-web-phase1 --no-ff \
  -m "merge: consolidar refatoração fase 1 (remoção upgrade web + saneamento Git)"

git push origin main
```

#### 4.3 Primeiro release do ciclo de retomada

```bash
cd /home/admin21ct/pmed2

# Após merge em main e CI verde
# Atualizar version em composer.json para o próximo incremento
# Ex: 2.1.4 → 2.2.0 (minor bump por set de mudanças)

git tag -a v2.2.0 -m "release: v2.2.0 — retomada pós-hiato, saneamento Git e consolidação CI/CD"
git push origin v2.2.0

# Isso dispara: docker-build.yml + cd-homolog.yml automaticamente
```

#### 4.4 Validar primeiro deploy em homolog

```bash
# Acompanhar via GitHub Actions:
# https://github.com/xlipesousa/pmed2/actions

# Ou via SSH no servidor homolog após deploy:
ssh <PMED2_HOM_SSH_USER>@<PMED2_HOM_SSH_HOST>
docker ps --filter name=pmed2
curl -s -o /dev/null -w "%{http_code}" http://localhost/
cat /var/www/pmed2/shared/compose/current_tag
```

#### 4.5 Gate manual para produção (após validação em homolog)

```bash
# Via GitHub Actions UI:
# Actions → CD Produção → Run workflow
# mode: deploy
# tag: v2.2.0
# (aguardar aprovação do environment "production")
```

**Critério de saída (DoD) Fase 4:**
- [ ] Branch `refactor/remove-upgrade-web-phase1` mergeada em `main`
- [ ] Tag de release criada e pipeline CI/CD verde end-to-end
- [ ] Deploy em homolog bem-sucedido e validado
- [ ] Deploy em produção aprovado e executado com sucesso
- [ ] Scripts legados categorizados e documentados

---

## 4. Matriz de Riscos

| Risco | Probabilidade | Impacto | Mitigação |
|-------|-------------|---------|-----------|
| Runner `pmed2-interno` inativo | Alta | Alto | Verificar e reativar antes de qualquer tag |
| Segredos GitHub CD não configurados | Média | Crítico | Auditar via Settings antes de disparar CD |
| Divergência de schema entre legado e container | Média | Alto | `migrate:status` antes de qualquer deploy |
| `.env` com credenciais versionadas no histórico | Alta (já ocorreu) | Crítico | Limpar histórico via `git filter-repo` após Fase 0 |
| Conflito de merge ao sincronizar main | Baixa | Médio | Fazer merge com `--no-ff` e resolver antes do push |
| `docker build` falha por dependência nova | Baixa | Alto | CI local antes de qualquer tag |

---

## 5. Backlog Técnico de Consolidação

| Item | Tipo | Prioridade |
|------|------|-----------|
| Limpar histórico Git do .env (git filter-repo) | Segurança | CRÍTICO |
| Adicionar `.secrets/` e `.secrets.example/db_*` ao `.gitignore` | Segurança | ALTO |
| Mover scripts legados para `legacy/` com documentação | Organização | MÉDIO |
| Criar `CONTRIBUTING.md` com o fluxo correto de deploy por tag | Governança | MÉDIO |
| Avaliar migração do backup cron para scheduler em container | Operacional | BAIXO |
| Adicionar `docker compose exec app php artisan health` ao pipeline | Qualidade | BAIXO |

---

## 6. Referências

| Arquivo | Papel |
|---------|-------|
| [.github/workflows/ci.yml](../.github/workflows/ci.yml) | Quality gate + empacotamento |
| [.github/workflows/docker-build.yml](../.github/workflows/docker-build.yml) | Build e push GHCR |
| [.github/workflows/cd-homolog.yml](../.github/workflows/cd-homolog.yml) | Deploy automático por tag em homolog |
| [.github/workflows/cd-prod.yml](../.github/workflows/cd-prod.yml) | Deploy manual com gate em produção |
| [deploy/compose.homolog.yml](../deploy/compose.homolog.yml) | Topologia homolog |
| [deploy/compose.prod.yml](../deploy/compose.prod.yml) | Topologia produção |
| [docker-compose.yml](../docker-compose.yml) | Stack local de desenvolvimento |
| [Dockerfile](../Dockerfile) | Build multi-stage |
| [docker/entrypoint.sh](../docker/entrypoint.sh) | Startup: migrations e storage link |
| [scripts/deploy.sh](../scripts/deploy.sh) | Fluxo legado (host) — manter como referência |
| [docs/upgrade-web-descontinuado.md](../docs/upgrade-web-descontinuado.md) | Decisão de remoção do upgrade web |
| [docs/producao-governanca-checklist.md](../docs/producao-governanca-checklist.md) | Governança de promoção por tag |
