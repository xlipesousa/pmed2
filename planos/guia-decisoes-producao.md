# Guia de Decisões — Migração para Docker (homologação → produção)

**Propósito:** registrar *por que* cada escolha foi feita na migração de homologação, para que a
migração de **produção** (`VM-21CT-4RM-PMED2-PROD-SERVER-PROD`) possa ser executada por qualquer
pessoa ou modelo, sem redescobrir o caminho e sem repetir os erros.

Leia junto com:
- `planos/plano-migracao-docker-homolog.md` — o plano executável
- `planos/ESTADO-MIGRACAO-HOMOLOG.md` — o que já foi feito, com evidência
- `docs/plano-execucao-faseado-retomada-docker.md` — histórico anterior (2026-05), com ressalvas na seção 4

---

## 1. Estado de partida (o que era verdade antes desta migração)

| Fato | Homologação | Produção |
|---|---|---|
| Docker instalado | **Não** | **Não** (confirmado em `scripts/result-diagnostico.txt`, 2026-07-10) |
| Modelo em uso | bare-metal: nginx 1.24 + php8.3-fpm + MySQL 8.0.46 no host | idem |
| Deploy | `releases/<timestamp>` + symlink `current` (`scripts/deploy.sh`) | idem |
| Versão rodando | 2.1.4 (release de 2026-03-04) | 2.1.4 (release de 2026-03-08) |
| Backup automático | **Nenhum** — sem cron, sem systemd timer | a verificar |
| Último backup | 2026-03-04 | a verificar |
| TLS | não há (HTTP puro) | a verificar |
| Firewall | `ufw` inativo, iptables ACCEPT | a verificar |

> **Lição registrada:** toda a infraestrutura Docker (Dockerfile, composes, workflows) foi escrita e
> validada **apenas na VM de desenvolvimento**. A documentação de 2026-05 afirma "6/6 containers UP" e
> "`/health` 200", mas essas evidências são de `localhost:8080` no dev — nunca de um servidor.
> **Antes de declarar qualquer ambiente migrado, exija evidência colhida naquele host.**

---

## 2. Decisões (ADR resumido)

### ADR-01 — MySQL permanece no host; não é containerizado
**Contexto:** o dev local roda MariaDB 11 em container (`docker-compose.yml`, service `db`). Os composes
de homolog/prod (`deploy/compose.*.yml`) **nunca tiveram** service `db` — sempre assumiram `DB_HOST` externo.
Homolog tem MySQL 8.0.46 no host com dados reais (13,4 MB).

**Decisão:** manter o MySQL do host. Confirma a decisão implícita já embutida nos composes.

**Razão:** dado é estado. Containerizar um banco com dado real é o passo de maior risco da migração e não
era necessário para o objetivo (pipeline funcional). Evita dump/restore, evita a troca MariaDB↔MySQL 8,
e mantém backup/gestão do banco no plano do SO.

**Consequência para produção:** repetir. **Não** adicionar service `db` ao `compose.prod.yml`.

**Custo aceito:** o ambiente não é 100% reproduzível/descartável como o dev. Se um dia quiser paridade
total, é uma migração própria, separada desta.

---

### ADR-02 — nginx do host vira proxy reverso; container publica em `127.0.0.1:8080`
**Contexto:** o nginx do host ocupa a porta 80. O `cd-homolog.yml` forçava `APP_HTTP_PORT=80`, e o
service `web` do compose publica `${APP_HTTP_PORT:-8080}:80` — colisão garantida (`address already in use`).

**Decisão:** container publica em `127.0.0.1:8080`; nginx do host faz `proxy_pass` para ele.

**Razão:** o cutover vira uma troca de arquivo de configuração do nginx, com rollback em segundos
(`cp` do `.bak` + `reload`). O bare-metal nunca precisa ser parado durante a validação. Além disso
mantém um ponto único no host para TLS/certbot no futuro — hoje inexistente, mas necessário em produção.

**Bind em loopback é deliberado:** `127.0.0.1:8080:80`, não `8080:80`. Como `ufw` está inativo e o
iptables é ACCEPT, publicar em `0.0.0.0` exporia a aplicação diretamente na rede, contornando o nginx.

**Consequência para produção:** repetir. Atenção: `compose.prod.yml` tem default `APP_HTTP_PORT:-80`
(homolog usa 8080), mas **ambos os workflows sobrescrevem para 80** — corrigir os dois.

---

### ADR-03 — Adotar Redis para cache e fila
**Contexto:** os composes já traziam `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis` e um service `redis`.
O homolog real roda `file`/`file`/`database` e **não tem worker de fila nem scheduler** — não há cron
nem systemd unit; ninguém consome a fila e ninguém roda o backup.

**Decisão:** manter o compose como escrito e adotar Redis.

**Razão:** o ganho não é o Redis em si — é passar a **ter** `queue` e `scheduler` rodando. O
`app/Console/Kernel.php` agenda `scripts/backup.sh` diariamente; sem container `scheduler`, esse
agendamento nunca executou. Foi exatamente isso que deixou homologação **5 meses sem backup**.

**Consequência para produção:** repetir, **mas** verificar antes se há jobs pendentes na tabela `jobs`
(homolog não tinha: `queue:failed` = vazio). Se houver, drenar antes de trocar o driver, senão os jobs
ficam órfãos.

**Efeito colateral aceito:** sessões são reiniciadas no corte (usuários deslogam). Aceitável em
homologação; **em produção, comunicar antes**.

---

### ADR-04 — Cutover paralelo, nunca big-bang
**Decisão:** subir a stack Docker em porta alternativa com o bare-metal ainda servindo; validar; só então
virar o tráfego.

**Razão:** se algo falhar na validação, nada foi perdido — o ambiente antigo nunca parou. O custo é uma
etapa a mais; o benefício é que a janela de indisponibilidade real é o tempo de um `systemctl reload nginx`.

**Consequência para produção:** obrigatório. Em produção não existe "tentar e ver".

---

### ADR-05 — `user: "0:0"` nos containers que leem o secret *(dívida técnica assumida)*
**Contexto:** o `cd-homolog.yml` grava `shared/secrets/db_password` com `chmod 600`, dono do usuário SSH
(`admin21ct`, uid 1000). O container roda como `www-data` da imagem Alpine, que é **uid 82** — e o
`www-data` do host Ubuntu é **uid 33**. Nenhum deles lê um arquivo `600` do uid 1000.

**Decisão (homologação):** `user: "0:0"` em `app`/`queue`/`scheduler`, mesma solução já usada no dev local.

**Razão:** é a solução conhecida, já exercida, e desbloqueia a migração. O dev local documenta o mesmo
problema na skill `run-pmed2` ("não 'conserte' afrouxando a permissão do secret").

**⚠️ Isto é dívida, não solução.** Rodar o PHP-FPM como root em produção é indesejável.
**Alternativas para produção, em ordem de preferência:**
1. Gravar o secret com `chown 82:82` + `chmod 400` no host (o uid 82 dentro do container é o `www-data`
   da imagem) — mantém least-privilege, mas exige `sudo chown` no step do workflow.
2. Passar a senha como variável de ambiente no `.env.<amb>` em vez de arquivo — mais simples, porém
   contraria a ADR-07 (segredo por arquivo) e reintroduz o risco de vazamento em log.
3. Manter `0:0` e compensar com hardening do host.

**Decidir explicitamente antes do cutover de produção. Não herdar por inércia.**

---

### ADR-06 — `APP_KEY` é propagado a partir do `shared/.env` do servidor
**Contexto:** **nenhum** compose de homolog/prod passava `APP_KEY`, e a imagem não contém `.env`
(excluído pelo `.dockerignore`). O app subiria sem chave de criptografia estável.

**Decisão:** o workflow lê `APP_KEY` do `shared/.env` do servidor (via `read_env`) e propaga ao compose.

**Razão crítica:** tem que ser **a chave que já existe**. Gerar uma nova invalida todas as sessões e
torna ilegível qualquer dado encriptado persistido no banco (casts `encrypted`, tokens, etc.).

**Consequência para produção:** confirmar `grep -c '^APP_KEY=base64:' /var/www/pmed2/shared/.env` = 1
**antes** de disparar o deploy. Se faltar, **parar** — não gerar chave nova.

---

### ADR-07 — Segredos por arquivo, não por variável de ambiente
**Contexto:** decisão herdada (commit `3070dff0`); `config/database.php` resolve `DB_PASSWORD` ou, em
fallback, lê `DB_PASSWORD_FILE`. O `docker/entrypoint.sh` faz o mesmo.

**Decisão:** manter.

**Reforço aprendido nesta sessão:** em 2026-08-08 um fragmento da senha real vazou em texto puro no log
do GitHub Actions. Causa: o workflow interpolava `${{ secrets.*_DB_PASSWORD }}` **direto no script `run:`**;
como a senha continha `$`, o bash tentou expandi-la e abortou com `unbound variable`, revelando o pedaço.
A máscara do GitHub não protegeu porque só redige o valor **completo**.

**Regra derivada (vale para qualquer secret):** nunca interpolar secret dentro do corpo de um `run:`.
Sempre passar via bloco `env:` do step e referenciar como `"${VAR}"`. Corrigido em `433c46f0` para
homolog e produção.

---

### ADR-08 — MySQL escuta na subnet Docker fixa do compose, com grant restrito a ela
**Contexto:** `DB_HOST=127.0.0.1` no `shared/.env`. Dentro de um container, isso aponta para o **próprio
container**. O MySQL do host só escuta em loopback e os grants existem apenas para `pmed2user@127.0.0.1`
e `pmed2user@localhost`.

**Decisão original (revisada):** a primeira versão do script de provisionamento descobria o gateway via
`ip -4 addr show docker0` (bridge padrão, tipicamente `172.17.0.1`) e configurava `bind-address`/grant
com base nele. **Isso estava errado** — `docker0` é a bridge *padrão* do Docker; `deploy/compose.homolog.yml`
declara uma rede **custom** própria (`networks: pmed2:`), que por padrão recebe uma subnet *diferente*,
atribuída dinamicamente pelo Docker só na primeira vez que `docker compose up` roda. Configurar o MySQL
para a subnet de `docker0` deixaria o grant apontando para uma rede que os containers reais do compose
nunca usam — um gate de conectividade (T2.6) rodado com `docker run` "solto" (sem `--network`) também cai
na bridge padrão por default, então **passaria mesmo com o grant errado**, mascarando o problema até o
primeiro deploy de verdade.

**Correção aplicada (v1 — subnet fixa via `ipam.config`, insuficiente sozinha):** primeiro fixamos a
subnet da rede `pmed2` diretamente no compose (`networks.pmed2.ipam.config.subnet`), deixando o Docker
criar a rede na primeira subida. **Isso ainda quebrou** — testado ao vivo no servidor: o MySQL não sobe
com `bind-address` apontando para um IP que não existe em nenhuma interface de rede. `docker network
create` (ou o primeiro `docker compose up`) é o que efetivamente cria a interface bridge e atribui o
gateway; até isso acontecer, `10.219.10.1` não existe em lugar nenhum, e `systemctl restart mysql` falha
("Job for mysql.service failed"). Como o provisionamento (T2.4, que configura o MySQL) roda **antes** do
primeiro deploy (F3, que é quando o compose criaria a rede), o MySQL sempre seria configurado cedo demais.

**Correção final aplicada:** a rede passou a ser **externa e persistente**, criada explicitamente pelo
script de provisionamento (`docker network create --subnet=10.219.10.0/24 pmed2-homolog-net`, seção 2,
**antes** do MySQL ser configurado na seção 3) — isso cria a interface bridge e o gateway imediatamente,
mesmo sem nenhum container rodando. `deploy/compose.homolog.yml`/`compose.prod.yml` referenciam essa rede
como `external: true`, então o `docker compose up` (F3) **reaproveita** a rede já criada em vez de tentar
criar a dele própria — o que o Docker recusaria de qualquer forma (duas redes não podem reivindicar a
mesma subnet simultaneamente). Subnets `10.219.10.0/24` (homolog) e `10.219.20.0/24` (produção),
propositalmente diferentes por higiene, fora do range que o Docker usa para auto-atribuição
(`172.17.0.0/16`–`172.31.0.0/16`). Com isso, `bind-address = 127.0.0.1,10.219.10.1` e o grant para
`pmed2user@'10.219.10.%'` ficam corretos desde o provisionamento, e o gate T2.6 passou a rodar na mesma
rede persistente (não mais numa rede efêmera de teste nem na bridge padrão), validando o caminho real.

**Lição para produção:** subnet fixa sozinha (via `ipam.config`) não basta quando algo fora do Docker
(o MySQL do host) depende do gateway existir *antes* do primeiro `docker compose up`. Sempre que uma
configuração de host depende de um IP de rede Docker, a rede precisa ser criada explicitamente e cedo —
nunca deixar para o compose criar implicitamente na hora do deploy.

**Correção adicional — `host.docker.internal:host-gateway` não funciona com rede custom:** o gate T2.6,
já testando na rede certa, ainda falhou ao vivo com `ERROR 2003: Can't connect ... (111)`. Causa:
`--add-host=host.docker.internal:host-gateway` (usado em `extra_hosts` no compose e no próprio script)
resolve para o gateway da bridge **padrão** do Docker (`docker0`, `172.17.0.1`), **não** para o gateway
da rede custom `pmed2` a que o container está anexado — mesmo passando `--network` explicitamente.
Confirmado com `getent hosts host.docker.internal` dentro de um container na rede `pmed2-homolog-net`:
resolvia para `172.17.0.1`, onde nada escuta (MySQL só está em `127.0.0.1` e `10.219.10.1`), daí
"connection refused". Como a subnet e o gateway da rede `pmed2` já são fixos e conhecidos, a correção foi
parar de depender do valor mágico `host-gateway` e apontar `host.docker.internal` direto para o IP fixo:
`extra_hosts: ["host.docker.internal:10.219.10.1"]` em vez de `["host.docker.internal:host-gateway"]`
(mesma mudança em `compose.homolog.yml`, `compose.prod.yml` e no gate do script de provisionamento).
Esse comportamento do Docker (host-gateway atrelado à bridge padrão, não à rede do container) não é
documentado com clareza e teria quebrado o deploy real do mesmo jeito que quebrou o gate — o gate cumpriu
exatamente a função de pegar isso antes de produção real ser afetada.

**Consequência para produção:** já resolvido preventivamente — `compose.prod.yml` já usa a subnet fixa
`10.219.20.0/24`. Não presumir `docker0`; usar sempre a subnet declarada no compose como fonte da verdade.
Em produção, considerar também ativar `ufw`.

**Decisão relacionada:** `COMPOSE_PROJECT_NAME=pmed2` também foi fixado (exportado) em todo lugar que
invoca `docker compose` (`cd-homolog.yml`, `scripts/verificar-stack-homolog.sh`) — sem isso, o nome do
projeto (que prefixa volumes e a rede) dependeria do diretório de trabalho da sessão SSH no momento da
execução, podendo variar entre deploys e fazer o Compose recriar volumes do zero em vez de reaproveitar
o volume de código já populado pelo `app-init`.

---

## 3. Armadilhas de verificação (por que quase demos por bom o que estava quebrado)

| Armadilha | Por quê | O que usar no lugar |
|---|---|---|
| `/health` retorna 200 | É uma rota que devolve **JSON estático** (`routes/web.php:24`). Não toca no banco. Passa com o DB inacessível. | `php artisan migrate:status` dentro do container |
| `curl http://127.0.0.1/health` durante o cutover paralelo | Na porta 80 quem responde é o **nginx bare-metal**. Falso-positivo: valida o ambiente antigo achando que é o novo. | Bater direto em `127.0.0.1:8080` |
| `curl -I .../login` no workflow | `-I` sem `-f` **não falha** em HTTP 500. | `curl -fsS` + `grep -c csrf-token` |
| Healthcheck logo após `up -d` | Sem retry, dispara o `trap ERR` → **rollback espúrio** mesmo com tudo correto. | Loop com timeout |
| Tag verde no `docker-build` | `docker-build` e `cd-homolog` disparam **em paralelo** na mesma tag; não há `needs`/`workflow_run`. O `pull` pode rodar antes da imagem existir. | Retry no `pull` |

---

## 4. Ressalvas sobre a documentação anterior

`docs/plano-execucao-faseado-retomada-docker.md` (2026-05) é útil como histórico, mas:

- Declara Fases A–E e G como **VERDE** com base em evidências colhidas **no dev**, não em servidor.
- **Nunca menciona** que o Docker não estava instalado em nenhum servidor. É o maior buraco do registro.
- **Nunca trata** da colisão da porta 80 nem do `DB_HOST=127.0.0.1` inalcançável de dentro do container.
- Contém contradição interna sobre qual compose é canônico: a Fase E decidiu
  `docker-compose.retomada.yml`; as "Further Considerations" recomendaram o oposto
  (`docker-compose.yml`). Na prática os dois seguem versionados, e o `docker-compose.yml` — que é o
  que roda de fato no dev e o que a skill `run-pmed2` documenta — **nunca recebeu** o header de
  deprecação previsto. **Pendência aberta.**
- A Fase F (remoção dos scripts legados) previa `rm` em jun/2026. O que aconteceu de fato foi *untrack*
  em jul/2026 (`e9d8c0db`): os arquivos saíram do repositório mas continuam no disco das VMs.

---

## 5. Roteiro para produção (derivado, **não** executar sem aprovação)

Pré-condições obrigatórias:
- [ ] Homologação estável em Docker por pelo menos um ciclo completo, **incluindo um backup gerado pelo `scheduler`**
- [ ] ADR-05 (`user: "0:0"`) decidida explicitamente para produção
- [ ] Janela de manutenção comunicada (ADR-03: usuários serão deslogados)
- [ ] Backup do banco de produção **verificado com `gunzip -t`**, guardado fora de `/var/www`
- [ ] Snapshot da VM de produção
- [ ] Confirmado `APP_KEY` em `/var/www/pmed2/shared/.env` de produção (ADR-06)
- [x] Subnet fixa da rede `pmed2` já definida em `compose.prod.yml` (`10.219.20.0/24`, ADR-08) — não depende de descoberta em runtime
- [ ] **Criar a rede `pmed2-prod-net` explicitamente ANTES de configurar o MySQL** (`docker network create
  --subnet=10.219.20.0/24 pmed2-prod-net`) — em homolog, pular esse passo e deixar o compose criar a rede
  implicitamente causou o MySQL entrar em loop de crash (bind num IP que ainda não existia em nenhuma
  interface). Ver ADR-08 e o Log de ocorrências de `ESTADO-MIGRACAO-HOMOLOG.md` (2026-08-10 18:53–19:13)
  para o relato completo do incidente.
- [ ] Confirmar que `extra_hosts` usa o IP fixo do gateway (`host.docker.internal:10.219.20.1`), **nunca**
  `host.docker.internal:host-gateway` — esse valor mágico resolve para a bridge padrão do Docker, não
  para a rede `pmed2`, e falha silenciosamente até o teste de conectividade real (confirmado ao vivo em
  homolog, ver ADR-08)
- [ ] Secret `PMED2_PROD_SSH_KNOWN_HOSTS` regenerado **a partir do runner** (ver §6)
- [ ] **Usuário SSH de deploy de produção (secret `PMED2_PROD_SSH_USER`) adicionado ao grupo `docker`**
  (`sudo usermod -aG docker <usuário>`) — em homolog, o Docker foi instalado como root via Teleport, mas
  isso não adiciona automaticamente nenhum outro usuário ao grupo `docker`. Sem isso, o primeiro deploy
  falha com `permission denied ... docker.sock`, mesmo com tudo mais correto.
- [ ] **Secrets `PMED2_PROD_GHCR_USER`/`PMED2_PROD_GHCR_TOKEN` criados antes do primeiro disparo de
  `cd-prod.yml`** — o pacote GHCR é privado; sem esses secrets o `docker login` do workflow fica
  condicional e pula silenciosamente, e o `pull` falha com `unauthorized`. Gerar um PAT classic com
  escopo `read:packages` (registrar validade para rotação futura — 90 dias em homolog).

**Confirmado ao vivo em homologação (2026-08-10, F3 completa):** `cd-homolog.yml` fechou **100% verde
de ponta a ponta** pela primeira vez na história do projeto, na 3ª tentativa (as duas primeiras
falharam exatamente nos dois pontos acima — grupo `docker` e secrets GHCR — e foram corrigidas em
minutos, sem precisar alterar nenhum código ou workflow). Essas duas pré-condições evitam repetir
esse mesmo ciclo de tentativa-e-erro em produção.

**Confirmado ao vivo em homologação (2026-08-10, F2 completa):** a sequência
"instalar Docker → criar rede externa com subnet fixa → configurar bind-address do MySQL → grant →
gate de conectividade com IP fixo (não `host-gateway`) → confirmar APP_KEY" rodou do início ao fim sem
erros na segunda tentativa (depois de 2 incidentes corrigidos — ver ADR-08). O script
`scripts/provisionar-docker-homolog.sh` é o roteiro de referência a adaptar para produção (trocar
subnet/nomes de `homolog` para `prod`).

Sequência: mesma do plano de homologação (F0→F5), trocando `homolog`→`prod`, com duas diferenças:
1. `cd-prod.yml` é `workflow_dispatch` com environment `production` protegido — o disparo é manual e
   passa por aprovação. Não há deploy automático por tag.
2. `cd-prod.yml` tem `mode: rollback` + `rollback_tag`, que homolog não tem — testar o rollback
   **antes** de precisar dele.

---

## 6. Pendências herdadas (fora do escopo desta migração, mas registradas)

| # | Pendência | Gravidade |
|---|---|---|
| P-1 | **Homolog e produção têm as mesmas chaves de host SSH** (mesmo `ssh-rsa`, `ecdsa`, `ed25519`) — indício de VMs clonadas de um template com as chaves já geradas, em vez de cada uma gerar as suas no primeiro boot. Enfraquece o pinning de `known_hosts`. | Alta |
| P-2 | O valor de `PMED2_HOM_SSH_HOST` **não é** o nome de exibição do Teleport. `known_hosts` hasheado é calculado sobre a string exata da conexão — por isso escanear pelo Teleport gerou hashes que nunca casavam. Regenerar **sempre a partir do runner**. | Média (resolvida, mas repetível) |
| P-3 | Sem TLS em homologação (HTTP puro), sem certbot instalado. | Média |
| P-4 | `ufw` inativo e iptables ACCEPT em homologação. | Média |
| P-5 | `docker-compose.yml` × `docker-compose.retomada.yml` sem canônico definido (ver §4). | Baixa |
| P-6 | ~~`planos/` e `docs/` não são versionados~~ — resolvido em 2026-08-10: este guia passou a ser exceção versionada (§7). Os demais arquivos de `planos/` seguem locais por design. | Resolvida |
| P-7 | Anti-pattern "carrega tabela inteira e filtra 6× no Blade" em `PacotesController::index()` e correlatos; mitigado por seleção de colunas, não resolvido. Causou 500 por memória em produção na v2.1.4. | Média |
| P-8 | `git filter-repo` para limpar `.env` do histórico Git — marcado CRÍTICO em `planos/plano-retomada.md`, nunca executado. | Alta |

---

## 7. Sobre o versionamento deste guia

`planos/` está no `.gitignore` como `/planos/*` (o resto da pasta — plano de execução, estado vivo da
migração — permanece local, por decisão do commit `e9d8c0db`).

**Decisão (2026-08-10):** este arquivo é a exceção — `!/planos/guia-decisoes-producao.md` — e passa a
ser versionado, acompanhando o repositório. Não contém segredos, apenas decisões técnicas e seus
motivos, então não há risco em publicá-lo. Os demais arquivos de `planos/` (plano de execução, estado
da migração tarefa a tarefa) seguem locais, por não terem valor de consulta futura fora desta migração
pontual.
