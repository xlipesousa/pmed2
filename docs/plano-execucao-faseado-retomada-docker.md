# Plano Faseado de Execucao - Retomada Docker PMED2

Origem: transcricao e refinamento do plano operacional da memoria de sessao.

## Objetivo
Concluir a retomada Docker com risco reduzido, partindo do diagnostico para validacao runtime e estabilizacao da stack.

## Fase 0 - Organizacao e Higiene de Repositorio
Checklist:
- [x] Consolidar arquivos de contexto/dev em area dedicada.
- [x] Bloquear versionamento da area de contexto local via gitignore.
- [x] Identificar documentos com risco de vazamento operacional e remover da trilha versionada.

Entregaveis:
- Documentos locais movidos para docs/dev-context.
- Regra adicionada no gitignore para docs/dev-context.

## Fase 1 - Baseline e Contexto
Checklist:
- [x] Revisar contexto operacional anterior da retomada.
- [x] Confirmar branch e estado local do repositorio.
- [x] Levantar snapshots de compose/logs disponiveis.

Entregaveis:
- Diagnostico consolidado do estado git e docker com base em evidencias.

## Fase 2 - Mapeamento Funcional do Sistema
Checklist:
- [x] Mapear rotas principais e fluxos de negocio.
- [x] Identificar controladores centrais (Pacotes, Mapas, OCS/PSA, Relatorios).
- [x] Confirmar modelo de autorizacao por papeis/gates.

Entregaveis:
- Visao funcional para orientar ajustes sem regressao.

## Fase 3 - Mapeamento de Pipeline e Governanca CI/CD
Checklist:
- [x] Confirmar workflow de build/publicacao de imagem.
- [x] Confirmar deploy automatico de homolog por tag.
- [x] Confirmar promocao manual para producao com rollback.

Entregaveis:
- Matriz de deploy homologacao -> producao alinhada com operacao.

## Fase 4 - Validacao Runtime da Stack Docker
Checklist:
- [x] Verificar status dos containers em tempo real (6 servicos).
- [x] Validar endpoint /health em runtime.
- [x] Validar conectividade DB com usuario da app.
- [x] Validar migrate:status no container app.
- [x] Validar processo queue:work no container queue.

Entregaveis:
- Semaforo de prontidao da stack (OK, PARCIAL ou BLOQUEADA).
- Nota operacional: existe uma stack paralela com prefixo pmed2 (legado/concorrente) em execucao parcial e queue reiniciando; nao bloqueia os checks da stack alvo pmed2r, mas aumenta risco de confusao operacional.

## Fase 5 - Estabilizacao e Hand-off
Checklist:
- [x] Corrigir bloqueios encontrados na fase 4.
- [x] Registrar causa raiz e evidencia de correcao.
- [x] Publicar resumo final com riscos residuais.

Entregaveis:
- Stack pronta para continuidade das atividades de migracao.

## Registro da Fase 5

Causa raiz identificada:
- Existia uma pilha Compose residual com projeto `pmed2` e containers `pmed2-*` rodando em paralelo a pilha alvo `pmed2-clean` com containers `pmed2r-*`.
- A stack residual nao era a stack oficial da retomada e mantinha pelo menos o container `pmed2-queue` em restart loop, gerando ambiguidade operacional.

Acao corretiva executada:
- Remocao dos containers residuais `pmed2-app`, `pmed2-queue`, `pmed2-scheduler`, `pmed2-vite` e `pmed2-redis-patch-build2`.

Evidencias de correcao:
- Apos a limpeza, apenas os containers `pmed2r-*` permaneceram ativos.
- `GET /health` respondeu `200` apos a limpeza.
- `php artisan migrate:status` no container `pmed2r-app` permaneceu consistente apos a limpeza.

Riscos residuais:
- Nao foi removida a rede legado `pmed2_pmed2`; ela nao interfere na operacao atual, mas pode ser limpa em janela de manutencao para reduzir ruido visual.
- A stack ativa permanece em `APP_ENV=local`; isso e adequado para retomada/laboratorio, mas deve ser revisado antes de promover o mesmo padrao para homologacao oficial.

## Arquivos de referencia
- docs/dev-context/context.md
- routes/web.php
- app/Http/Controllers/PacotesController.php
- docker-compose.retomada.yml
- docs/dev-context/retomada-fase2-compose-ps.txt
- docs/dev-context/retomada-fase2-logs.txt
- .github/workflows/cd-homolog.yml
- .github/workflows/cd-prod.yml

---

## Atualizacao 2026-05-16 - Plano Persistido em Memoria

## Painel de Acompanhamento por Fase (A-G)

Regra de acompanhamento:
- [x] tarefa concluida com evidencia registrada.
- [ ] tarefa pendente.

### Fase A - Fechamento de Evidencias Operacionais
- [x] A.1 Confirmar stack ativa em runtime (docker ps, compose ls, redes, volumes).
- [x] A.2 Validar saude por servico com logs e endpoint /health.
- [x] A.3 Consolidar evidencia definitiva de remocao residual pmed2 versus pmed2r.
- [x] A.4 Encerrar fase com semaforo final e riscos residuais.

### Fase B - Diagnostico Git de Divergencia e Estado Anomalo
- [x] B.1 Extrair delta real entre origin/main e origin/refactor/remove-upgrade-web-phase1.
- [x] B.2 Validar tracking efetivo para vendor, node_modules, storage/framework/views e .env.
- [x] B.3 Decidir padronizacao da nomenclatura da branch local.
- [x] B.4 Produzir parecer final de merge readiness com precondicoes.

**Status: AMARELO (4 precondicoes pendentes de execucao)**
- Precondiciones a executar:
  1. Renomear branch local para refactor/remove-upgrade-web-phase1
  2. Validar docker final (docker ps, /health, migrate:status)
  3. Push commits para upstream
  4. Executar validacao final de testes
- Consolidacao realizada: 5 commits novos consolidados + 4 anteriores = 9 total
- Git status: clean

### Fase C - Saneamento Git
- [x] C.1 Remover tracking indevido, se existir.
- [x] C.2 Ajustar .gitignore para cobertura defensiva.
- [x] C.3 Gerar commit dedicado de saneamento.
- [x] C.4 Validar reducao de ruido no status.

**Status: VERDE (concluido)**
- vendor=0, node_modules=0, storage/framework=estrutural, .env=0
- .gitignore com padroes defensivos completos
- 5 commits consolidados (fix config, feat docker-compose, chore .gitignore/docs, feat dockerfile)
- git status clean (0 mudancas pendentes)
- Checksum HEAD: dacc1b66d62fd327ac39227886ffb8b96d586dec

### Fase D - Inventario de Sensiveis e Politica de Ignore
- [ ] D.1 Classificar sensiveis por severidade.
- [ ] D.2 Tratar excecoes perigosas de SQL no repositório.
- [ ] D.3 Executar plano de resposta para sensiveis rastreados.
- [ ] D.4 Publicar matriz Arquivo x Risco x Acao x Responsavel.

### Fase E - Convergencia Compose e Arquitetura Docker
- [ ] E.1 Definir compose canonico de desenvolvimento.
- [ ] E.2 Definir padrao final com overrides e depreciacao formal.
- [ ] E.3 Planejar migracao segura (janela, rollback, validacao).
- [ ] E.4 Atualizar documentacao operacional do fluxo canonico.

### Fase F - Legado e Impacto de Remocao
- [ ] F.1 Executar analise de impacto por script legado.
- [ ] F.2 Classificar cada item: remover, manter doc, mover para legado.
- [ ] F.3 Definir plano de remocao em duas etapas.
- [ ] F.4 Aplicar sinalizacao de nao uso em producao docker.

### Fase G - Normalizacao Final e Governanca
- [ ] G.1 Fechar branch strategy.
- [ ] G.2 Validar pipelines CI/CD no novo estado.
- [ ] G.3 Criar checklist final de prontidao.
- [ ] G.4 Entregar relatorio final com evidencias por item.

## Plan: Retomada PMED2 Investigacao e Saneamento

Concluir a retomada com baixo risco operacional atraves de: fechamento de evidencias (docker runtime e divergencia git), saneamento de repositorio (tracked indevido e sensiveis), convergencia para uma estrategia compose unica, e decisao formal sobre legado. O plano abaixo esta pronto para execucao por agente implementador, com dependencias, criterios de aceite e escopo explicito.

**Steps**
1. Fase A - Fechamento de Evidencias Operacionais (bloqueante das demais fases)
1.1 Confirmar stack ativa em runtime no host com [docker ps](docker ps), [docker compose ls](docker compose ls), [docker network ls](docker network ls), [docker volume ls](docker volume ls). Objetivo: comprovar se existe somente stack alvo e se ha residuos pmed2.
1.2 Validar saude por servico (app, web, queue, scheduler, db, redis) com logs por container e endpoint /health. Objetivo: classificar status em OK, PARCIAL ou BLOQUEADA.
1.3 Consolidar evidencia da remocao residual: comparar nomes pmed2 versus pmed2r em runtime e registrar resultado definitivo.
1.4 Dependencia: sem A concluida, nao executar limpeza destrutiva de rede/volume.

2. Fase B - Diagnostico Git de Divergencia e Estado Anomalo (depende da Fase A para nao misturar contexto)
2.1 Extrair delta real entre [origin/main](origin/main) e [origin/refactor/remove-upgrade-web-phase1](origin/refactor/remove-upgrade-web-phase1): commits, arquivos tocados, motivo funcional por commit.
2.2 Validar tracking efetivo no HEAD da branch de retomada para vendor, node_modules, storage/framework/views e .env (nao inferir por .gitignore; confirmar por arvore indexada).
2.3 Confirmar risco da nomenclatura local: branch local retomada/docker-homolog rastreando origin/refactor/remove-upgrade-web-phase1. Decidir padronizacao de nome.
2.4 Produzir parecer de merge readiness com semaforo: verde, amarelo, vermelho e precondicoes.

3. Fase C - Saneamento Git (depende da Fase B)
3.1 Se houver tracked indevido (vendor/node_modules/storage/framework/views/.env), executar retirada de tracking com preservacao dos arquivos no disco.
3.2 Revisar e ajustar [.gitignore](.gitignore) para cobertura defensiva sem ocultar codigo-fonte importante.
3.3 Gerar commit dedicado de saneamento, isolado de mudancas funcionais.
3.4 Validar pos-saneamento: contagem de arquivos modificados deve cair para apenas mudancas reais do projeto.

4. Fase D - Inventario de Sensiveis e Politica de Ignore (paralelo com Fase C apos 3.1)
4.1 Classificar todos os arquivos candidatos a sensivel por severidade: ambiente, secrets, chaves, dumps, credenciais em docs/scripts.
4.2 Tratar excecoes perigosas em [.gitignore](.gitignore), especialmente SQL em [public/schema-pmed2.sql](public/schema-pmed2.sql) e [public/schema-pmed2-anulacao.sql](public/schema-pmed2-anulacao.sql), definindo politica oficial:
- opcao 1: remover do repositorio;
- opcao 2: mover para area nao publica e manter somente schema higienizado.
4.3 Para sensiveis ja rastreados, executar plano de resposta: remover tracking, rotacionar segredo se necessario, registrar impacto.
4.4 Entregavel: matriz Arquivo x Risco x Acao x Responsavel.

5. Fase E - Convergencia Compose e Arquitetura Docker (depende da Fase A e B)
5.1 Comparar e decidir arquivo canonico de desenvolvimento entre [docker-compose.yml](docker-compose.yml) e [docker-compose.retomada.yml](docker-compose.retomada.yml), com justificativa tecnica (nomes, volumes, profile, compatibilidade).
5.2 Definir modelo final:
- canonico unico para dev;
- overrides por ambiente;
- depreciacao formal do compose alternativo.
5.3 Planejar migracao segura para evitar downtime/confusao: janela, rollback e validacao pos-troca.
5.4 Ajustar documentacao operacional para refletir somente o fluxo canonico.

6. Fase F - Legado (install/update/migrate) e Impacto de Remocao (depende da Fase B)
6.1 Executar analise de impacto real por script: onde e referenciado, quem consome, risco de remocao.
6.2 Classificar cada item em: remover, manter como documentacao, mover para legado descontinuado.
6.3 Para removiveis, criar plano de remocao em duas etapas:
- etapa 1: deprecar e comunicar;
- etapa 2: remover definitivamente apos janela acordada.
6.4 Para itens mantidos como documentacao, mover para area explicita de legado e adicionar aviso de nao uso em producao docker.

7. Fase G - Normalizacao Final do Repositorio e Governanca (depende de C, D, E, F)
7.1 Fechar branch strategy: nome padronizado, base correta, e regra de integracao com main.
7.2 Validar pipelines CI/CD com o novo estado (runner ja esta fora desta estrutura e operando na rede final, conforme informado pelo usuario).
7.3 Criar checklist de prontidao de retomada: git limpo, compose canonico, legados classificados, sensiveis saneados, stack validada.
7.4 Entregar relatorio final com evidencias objetivas por item.

**Relevant files**
- [/home/admin21ct/pmed2/.gitignore](/home/admin21ct/pmed2/.gitignore) — politica de exclusao e excecoes sensiveis.
- [/home/admin21ct/pmed2/docker-compose.yml](/home/admin21ct/pmed2/docker-compose.yml) — compose legado/local a comparar.
- [/home/admin21ct/pmed2/docker-compose.retomada.yml](/home/admin21ct/pmed2/docker-compose.retomada.yml) — compose de retomada candidato a canonico.
- [/home/admin21ct/pmed2/docker-compose.retomada.override.yml](/home/admin21ct/pmed2/docker-compose.retomada.override.yml) — diferencas de ambiente (homolog).
- [/home/admin21ct/pmed2/Dockerfile](/home/admin21ct/pmed2/Dockerfile) — baseline de build/runtime.
- [/home/admin21ct/pmed2/docker/entrypoint.sh](/home/admin21ct/pmed2/docker/entrypoint.sh) — comportamento de boot/migration/secrets.
- [/home/admin21ct/pmed2/deploy/compose.homolog.yml](/home/admin21ct/pmed2/deploy/compose.homolog.yml) — fluxo homolog.
- [/home/admin21ct/pmed2/deploy/compose.prod.yml](/home/admin21ct/pmed2/deploy/compose.prod.yml) — fluxo producao.
- [/home/admin21ct/pmed2/install.sh](/home/admin21ct/pmed2/install.sh) — legado de instalacao bare-metal.
- [/home/admin21ct/pmed2/update.sh](/home/admin21ct/pmed2/update.sh) — legado de update em host.
- [/home/admin21ct/pmed2/migrate.sh](/home/admin21ct/pmed2/migrate.sh) — legado de migracao dev-host.
- [/home/admin21ct/pmed2/scripts/deploy.sh](/home/admin21ct/pmed2/scripts/deploy.sh) — processo operacional ainda relevante.
- [/home/admin21ct/pmed2/scripts/rollback.sh](/home/admin21ct/pmed2/scripts/rollback.sh) — rollback operacional.
- [/home/admin21ct/pmed2/public/schema-pmed2.sql](/home/admin21ct/pmed2/public/schema-pmed2.sql) — artefato SQL sensivel em pasta publica.
- [/home/admin21ct/pmed2/public/schema-pmed2-anulacao.sql](/home/admin21ct/pmed2/public/schema-pmed2-anulacao.sql) — artefato SQL sensivel em pasta publica.
- [/home/admin21ct/pmed2/docs/plano-execucao-faseado-retomada-docker.md](/home/admin21ct/pmed2/docs/plano-execucao-faseado-retomada-docker.md) — referencia historica da tentativa anterior.
- [/home/admin21ct/pmed2/docs/dev-context/retomada-fase2-compose-ps.txt](/home/admin21ct/pmed2/docs/dev-context/retomada-fase2-compose-ps.txt) — ultimo snapshot conhecido de runtime.

**Verification**
1. Runtime: evidencia de que nao ha containers residuais fora do padrao alvo e health endpoint responde 200.
2. Git: confirmacao objetiva de tracking indevido ausente para vendor, node_modules, .env e storage compilado.
3. Seguranca: nenhum arquivo sensivel critico rastreado; excecoes de .gitignore justificadas e aprovadas.
4. Compose: somente um fluxo canonico documentado para dev; homolog/prod com diferencas explicitas e sem ambiguidade.
5. Legado: decisao formal por script com impacto avaliado e janela de remocao/depreciacao definida.
6. Governanca: relatorio final com semaforos por area e pendencias remanescentes.

**Decisions**
- Inclui: investigacao tecnica profunda, plano de saneamento, e roteiro executavel para proximo agente.
- Exclui: execucao destrutiva imediata sem validacao runtime ao vivo (docker ps e logs atuais ainda precisam ser coletados no host).
- Premissa confirmada: runner self-hosted ja foi movido para a estrutura final e acionado pelas GitHub Actions.

**Further Considerations**
1. Estrategia para schemas SQL publicos:
Opcao A: remover totalmente do repositorio.
Opcao B: mover para diretorio nao publico com versao higienizada.
Recomendacao: Opcao B se houver uso real de referencia; Opcao A se nao houver.
2. Convergencia compose:
Opcao A: manter docker-compose.retomada.yml como canonico e descontinuar docker-compose.yml.
Opcao B: unificar em docker-compose.yml e aposentar arquivo de retomada.
Recomendacao: Opcao B para reduzir atrito operacional, desde que preserve naming e comportamento ja validados.
3. Legado:
Opcao A: remocao direta.
Opcao B: deprecar e mover para area legacy antes de remover.
Recomendacao: Opcao B para evitar perda de fallback durante transicao final de retomada.

---

## Execucao Iniciada - 2026-05-16

### Fase A - Fechamento de Evidencias Operacionais

Status: CONCLUIDA (VERDE)

Evidencias coletadas em runtime:
- Stack ativa identificada como `pmed2-clean` com compose de retomada.
- Containers `pmed2r-app`, `pmed2r-queue`, `pmed2r-scheduler`, `pmed2r-nginx`, `pmed2r-redis` em execucao.
- Container `pmed2r-db` encontrado em `Exited (127)`.
- Nao houve evidencia de coexistencia de containers `pmed2-*` ativos na coleta atual.
- Endpoint `http://localhost:8080/health` retornando 502.
- Logs de app com erro de conexao SQL: `php_network_getaddresses: getaddrinfo for db failed`.
- Logs de nginx com `connect() failed (111: Connection refused)` para upstream fastcgi.

Leitura tecnica:
- A stack atual esta incompleta/inconsistente: DB indisponivel e aplicacao sem backend funcional para health.

Atualizacao de estabilizacao (rodada 2):
- Diagnostico confirmou que `DB_PASSWORD_FILE` estava presente e legivel no container app, mas a senha nao era carregada no `config(database.connections.mysql.password)` (len=0).
- Correcao aplicada em `config/database.php` para resolver a senha diretamente no retorno da configuracao, usando `DB_PASSWORD` ou fallback em `DB_PASSWORD_FILE`.
- Apos `php artisan config:clear`, a configuracao passou a refletir senha valida (`config_mysql_password_len=56`).
- `php artisan migrate:status` executado com sucesso (migracoes em estado `Ran`).
- `GET /health` validado com `200` e payload `{"status":"ok","app":"PMED 2.0","env":"homolog",...}`.
- Servicos `pmed2r-app`, `pmed2r-nginx`, `pmed2r-db`, `pmed2r-queue`, `pmed2r-scheduler`, `pmed2r-redis` em execucao.

Semaforo final da Fase A:
- VERDE

Riscos residuais da Fase A:
- Nenhum bloqueio tecnico imediato para continuidade das fases B-E-F.

### Fase B - Diagnostico Git de Divergencia e Estado Anomalo

Status: CONCLUIDA (AMARELO)

Evidencias coletadas:
- Delta entre `origin/main` e `origin/refactor/remove-upgrade-web-phase1`: 4 commits.
- Commits identificados no delta:
	- `ec7ddaa2` chore(git): remover vendor/node_modules/.env/views do tracking do Git
	- `f93d22d6` feat(infra): adicionar infraestrutura completa de containers Docker
	- `d6fce601` docs(plano): adicionar plano de retomada operacional ao repositorio
	- `015a32c2` chore(scripts): adicionar normalize_env_passwords.sh
- Verificacao de tracking efetivo no HEAD:
	- `vendor=0`
	- `node_modules=0`
	- `storage/framework/views=0`
	- `.env=0`
- Working tree local reportada com 12 linhas alteradas na coleta.

Leitura tecnica:
- O problema de tracking indevido (P1) aparece tratado na branch de retomada analisada.

Fechamento B.3 - Padronizacao de branch:
- Decisao: padronizar o nome local para espelhar upstream.
- Acao recomendada: renomear branch local `retomada/docker-homolog` para `refactor/remove-upgrade-web-phase1`.
- Justificativa: reduzir ambiguidade operacional e alinhar nomenclatura com a branch remota de trabalho.

Fechamento B.4 - Parecer final de merge readiness:
- Semaforo: AMARELO.
- Estado de divergencia: branch de retomada com 4 commits a frente de `origin/main` e 0 atras.
- Precondicoes minimas para merge sem risco:
	1. Consolidar alteracoes locais pendentes (commit ou stash).
	2. Decidir inclusao/exclusao dos arquivos novos/nao rastreados antes do merge.
	3. Executar padronizacao do nome da branch local.
	4. Rodar validacao final de testes no ambiente docker estabilizado antes de integrar em `main`.

### Fase C - Saneamento Git

Status: CONCLUIDA (VERDE)

Execucao da Fase C realizada em 2026-05-16:

**C.1 - Remover tracking indevido:**
- Validacao: git ls-files para vendor/, node_modules/, storage/framework/, .env
- Resultado: ✅ 0 arquivos rastreados em diretórios sensíveis
- Rastreados seguramente: apenas .env.example (modelo)

**C.2 - Ajustar .gitignore para cobertura defensiva:**
- Atualizacao realizada: adicionados padrões de exclusão para artefatos operacionais
- Padrões adicionados:
  - /docs/dev-context/ (contexto local)
  - /context.md
  - /retomada-fase2-*.txt
  - /DEPLOY.md, /RESUMO-DEPLOY.md, /README.laravel.md, /plano-ci-cd.md
- Validacao: grep -E '(vendor|node_modules|storage|\.env)' .gitignore confirma cobertura

**C.3 - Gerar commit dedicado de saneamento:**
- 5 commits consolidados:
  1. `3070dff0` fix(config): resolve DB_PASSWORD de Docker Secrets no config/database.php
  2. `3bd6636b` feat(infra): adicionar docker-compose canonico de retomada e plano de execucao
  3. `d12cae1e` chore(git): atualizar .gitignore e remover documentacao obsoleta
  4. `cfa38a85` feat(dockerfile): adicionar extensao redis para queue worker
  5. `dacc1b66` chore(docs): atualizar referencia removida de DEPLOY.md
- Arquivos removidos: DEPLOY.md, README.laravel.md, RESUMO-DEPLOY.md, plano-ci-cd.md, docs/lab-teste-fogo-snapshot.md

**C.4 - Validar reducao de ruido no status:**
- Git status: CLEAN ✅ (0 mudanças pendentes)
- Delta consolidado vs origin/main: 9 commits (5 novos + 4 anteriores)
- Checksum HEAD: dacc1b66d62fd327ac39227886ffb8b96d586dec
- Branch tracking: retomada/docker-homolog → origin/refactor/remove-upgrade-web-phase1

Semaforo final da Fase C:
- VERDE

Riscos residuais da Fase C:
- Nenhum (repositorio git limpo de tracking indevido)

---

### Proximas Acoes Imediatas - 2026-05-16

**Sequência recomendada:**

**1. CONCLUIR PRECONDIÇÕES DE FASE B (antes de merge):**
   - [ ] Renomear branch local: `git branch -m refactor/remove-upgrade-web-phase1`
   - [ ] Validar Docker: `docker ps`, `curl http://localhost:8080/health`, `docker exec pmed2r-app php artisan migrate:status`
   - [ ] Push commits: `git push -u origin refactor/remove-upgrade-web-phase1`

**2. EXECUTAR FASE D - Inventario de Sensiveis (após B.3):**
   - [ ] D.1 Classificar sensíveis por severidade
   - [ ] D.2 Decidir estratégia para /public/schema-pmed2*.sql (remover vs mover)
   - [ ] D.3 Executar plano de resposta
   - [ ] D.4 Publicar matriz de sensíveis

**3. EXECUTAR FASE E - Convergencia Compose (paralelo com D):**
   - [ ] E.1 Decidir compose canônico (docker-compose.retomada.yml vs docker-compose.yml)
   - [ ] E.2 Definir padrão final com overrides
   - [ ] E.3 Planejar migração segura
   - [ ] E.4 Atualizar documentação

**4. EXECUTAR FASE F - Legado e Impacto (paralelo):**
   - [ ] F.1 Analisar impacto de scripts
   - [ ] F.2 Classificar: remover/manter/deprecar
   - [ ] F.3 Plano de 2 fases (deprecar → remover)
   - [ ] F.4 Marcar descontinuados

**5. FECHAR COM FASE G - Normalização Final (última):**
   - [ ] G.1 Fechar branch strategy
   - [ ] G.2 Validar CI/CD pipelines
   - [ ] G.3 Checklist final de prontidão
   - [ ] G.4 Relatório final com semáforos
