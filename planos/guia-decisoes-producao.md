# Guia de Decisões — Migração para Docker (homologação → produção)

**Status: as duas migrações estão concluídas.** Homologação (2026-08-10) e produção (2026-08-11)
rodam a stack Docker em produção real, com `cd-homolog.yml`/`cd-prod.yml` fechando verde de ponta a
ponta e validação com dados reais (`/pacotes` sem 500 nos dois ambientes).

**Propósito:** registrar *por que* cada escolha foi feita em cada migração — o que se repetiu sem
incidente (prova de que a lição foi bem capturada) e o que surpreendeu mesmo assim (prova de que
nenhum ambiente é 100% previsível a partir do outro) — para qualquer pessoa ou modelo que precise
mexer nesta infraestrutura novamente, sem redescobrir o caminho.

Leia junto com:
- `planos/plano-migracao-docker-homolog.md` / `planos/plano-migracao-docker-producao.md` — os planos executáveis
- `planos/ESTADO-MIGRACAO-HOMOLOG.md` / `planos/ESTADO-MIGRACAO-PROD.md` — o que foi feito, com evidência
- `docs/plano-execucao-faseado-retomada-docker.md` — histórico anterior (2026-05), com ressalvas na seção 4

---

## 1. Estado de partida (o que era verdade antes desta migração)

| Fato | Homologação | Produção |
|---|---|---|
| Docker instalado | **Não** | **Não** (confirmado em `scripts/result-diagnostico.txt`, 2026-07-10) |
| Modelo em uso | bare-metal: nginx 1.24 + php8.3-fpm + MySQL 8.0.46 no host | idem |
| Deploy | `releases/<timestamp>` + symlink `current` (`scripts/deploy.sh`) | idem |
| Versão rodando | 2.1.4 (release de 2026-03-04) | 2.1.4 (release de 2026-03-08) |
| Backup automático | **Nenhum** — sem cron, sem systemd timer | **Nenhum** — mesmo padrão (confirmado no diagnóstico de 2026-08-11) |
| Último backup | 2026-03-04 | 2026-03-08 (5 meses antes da migração) |
| TLS | não há (HTTP puro) | não há (HTTP puro, sem domínio — `server_name _`, acesso por IP `10.122.8.15`; existe DNS `pmed2.4rm.eb.mil.br` apontando pro mesmo IP, descoberto só depois do cutover) |
| Firewall | `ufw` inativo, iptables ACCEPT | idem — **mas** existe um firewall corporativo separado (fora do host) que bloqueava egress para `ghcr.io`/Docker Hub por padrão, e só libera as portas 80/443 para o operador via Teleport (ver ADR-10) |

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

**Resultado prático (2026-08-10):** confirmado. Zero dump/restore de dados; o MySQL do host nunca foi
tocado além de `bind-address` (ADR-08) e um novo usuário/grant. Todas as migrations pré-existentes
continuaram `Ran` sem re-execução.

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

**Resultado prático (2026-08-10):** confirmado. Cutover real levou segundos (`nginx -t` + `reload`);
`cutover-nginx-homolog.sh` restaura o backup automaticamente se `nginx -t` falhar, então nunca houve
risco de deixar o nginx num estado inválido. Rollback testado disponível em 1 comando
(`sudo ./cutover-nginx-homolog.sh rollback`), não precisou ser usado.

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

**Resultado prático (2026-08-10):** `scheduler` e `queue` confirmados `Up` e saudáveis em
`verificar-stack-homolog.sh`. **T5.1 (confirmar que o backup diário de fato rodou) ainda pendente** —
só é verificável no dia seguinte ao cutover; acompanhar e fechar antes de considerar F5 encerrada.

---

### ADR-04 — Cutover paralelo, nunca big-bang
**Decisão:** subir a stack Docker em porta alternativa com o bare-metal ainda servindo; validar; só então
virar o tráfego.

**Razão:** se algo falhar na validação, nada foi perdido — o ambiente antigo nunca parou. O custo é uma
etapa a mais; o benefício é que a janela de indisponibilidade real é o tempo de um `systemctl reload nginx`.

**Consequência para produção:** obrigatório. Em produção não existe "tentar e ver".

**Resultado prático (2026-08-10):** validado no essencial — o bare-metal nunca foi parado, e o cutover
em si (troca do nginx) não teve nenhum risco por rodar em paralelo. **Ressalva importante, não prevista
no ADR original:** o isolamento entre os dois ambientes **não é total** — eles compartilham o mesmo
MySQL do host. O incidente de F2 (MySQL em loop de crash por um `bind-address` mal configurado, ver
ADR-08) afetou o bare-metal também: `/login` deu 500 por ~20 minutos, mesmo com a stack Docker nova
ainda nem tendo subido. **Lição para produção:** "paralelo" protege a aplicação nova de afetar a antiga,
mas não protege a antiga de mudanças feitas no host (banco, rede) durante o provisionamento — validar
esses passos com o mesmo cuidado que se validaria uma mudança em produção de verdade.

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

**Resultado prático (2026-08-10, homolog):** funcionou sem incidente — `app`/`queue`/`scheduler`
leram o secret normalmente como root. Nenhum problema de permissão observado.

**Dívida paga (2026-08-11):** decisão tomada e implementada em ambos os ambientes — a alternativa 1
("gravar o secret com permissão restrita ao uid do container"), mas via **ACL, não `chown`**.
`chown 82:82` foi descartado ao vivo: exige privilégio de root para trocar o **dono** de um arquivo,
e o usuário SSH de deploy **não tem sudo sem senha em produção** (confirmado por teste direto:
`sudo -n true` pediu senha) — inviável num step SSH não-interativo do workflow. `setfacl -m u:82:r`
resolve o mesmo problema sem precisar de root: é uma operação normal do **dono** do arquivo (o
usuário SSH que já grava o secret), concedendo leitura ao uid 82 (`www-data` da imagem Alpine) sem
trocar a posse. Só a instalação do pacote `acl` (uma vez, na provisão) precisa de sudo — e essa etapa
já é interativa por natureza (`apt-get install docker.io` também precisa).
Implementado em `cd-homolog.yml`/`cd-prod.yml` (step "Instalar segredo DB por arquivo") e validado
com `getfacl`: `user::rw-`, `user:82:r--`, `mask::r--`. `user: "0:0"` removido de `app`/`queue`/
`scheduler` nos dois composes — os containers agora rodam como `www-data` (uid 82), não root.
**ADR-05: Resolvida.**

---

### ADR-06 — `APP_KEY` é propagado a partir do `shared/.env` do servidor
**Contexto:** **nenhum** compose de homolog/prod passava `APP_KEY`, e a imagem não contém `.env`
(excluído pelo `.dockerignore`). O app subiria sem chave de criptografia estável.

**Decisão:** o workflow lê `APP_KEY` do `shared/.env` do servidor (via `read_env`) e propaga ao compose.

**Razão crítica:** tem que ser **a chave que já existe**. Gerar uma nova invalida todas as sessões e
torna ilegível qualquer dado encriptado persistido no banco (casts `encrypted`, tokens, etc.).

**Consequência para produção:** confirmar `grep -c '^APP_KEY=base64:' /var/www/pmed2/shared/.env` = 1
**antes** de disparar o deploy. Se faltar, **parar** — não gerar chave nova.

**Resultado prático (2026-08-10):** confirmado presente e único em todas as verificações (T2.7, T3.x).
Sessões e dados encriptados preservados através da migração — nenhuma queixa de logout forçado ou dado
ilegível após o cutover.

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

**Resultado prático (2026-08-10):** a regra se provou necessária de novo nesta sessão — todos os novos
steps escritos (backup, grants, GHCR login) seguiram o padrão `env:` desde o início, e nenhum novo
vazamento ocorreu apesar de várias senhas/tokens novos terem sido manipulados (senha do banco, GHCR
token). A regra derivada aqui é a única defesa real contra esse tipo de vazamento — vale reforçar em
qualquer novo workflow, inclusive fora desta migração.

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

**Resultado prático (2026-08-11, produção):** a sequência "rede externa antes do MySQL" funcionou
**sem nenhum incidente na 1ª tentativa** — diferença marcante frente aos 2 incidentes reais de
homolog. `docker network create --subnet=10.219.20.0/24 pmed2-prod-net` criou o gateway
`10.219.20.1` numa interface real (`br-ae3bab27c8c6`); `bind-address = 127.0.0.1,10.219.20.1` ficou
correto de primeira; o `systemctl restart mysql` não derrubou o bare-metal (`/health` respondeu 200
logo depois); o gate de conectividade com IP literal (`10.219.20.1`, não `host-gateway`) passou de
primeira. Confirma que as lições capturadas aqui, quando incorporadas *de antemão* no script (em vez
de descobertas ao vivo), eliminam o incidente por completo — não é sorte, é o script certo.

**Decisão relacionada:** `COMPOSE_PROJECT_NAME=pmed2` também foi fixado (exportado) em todo lugar que
invoca `docker compose` (`cd-homolog.yml`, `scripts/verificar-stack-homolog.sh`) — sem isso, o nome do
projeto (que prefixa volumes e a rede) dependeria do diretório de trabalho da sessão SSH no momento da
execução, podendo variar entre deploys e fazer o Compose recriar volumes do zero em vez de reaproveitar
o volume de código já populado pelo `app-init`.

---

### ADR-09 — `memory_limit` explícito na imagem, via `docker/php/pmed2.ini`
**Contexto:** a imagem base `php:8.3-fpm-alpine` não ativa nenhum `php.ini` (só entrega
`php.ini-production`/`php.ini-development`, sem copiar nenhum dos dois para `php.ini`). Sem isso, a
aplicação roda com o default compilado do PHP: `memory_limit=128M`. O php-fpm bare-metal de produção
tinha sido ajustado em hotfix para `512M` (`pool.d/www.conf`, `php_admin_value[memory_limit]`) depois
de `/pacotes` (`PacotesController::index()`, mesmo anti-pattern da P-7) causar 500 por estouro de
memória **em produção**, na v2.1.4.

**Decisão:** criar `docker/php/pmed2.ini` com `memory_limit = 512M` explícito, copiado para
`/usr/local/etc/php/conf.d/` no estágio `runtime` do `Dockerfile`.

**Razão:** 512M não foi escolhido às cegas — é o mesmo valor já validado ao vivo em produção pelo
hotfix anterior. Deixar isso implícito (dependendo do default da imagem) reintroduziria exatamente o
bug que o hotfix já tinha corrigido, só que silenciosamente, na migração para container.

**Resultado prático (2026-08-11):** validado localmente antes do deploy (`docker run --entrypoint
php-fpm ... -i` confirmou `Server API => FPM/FastCGI`, `Loaded Configuration File => (none)` sem o
arquivo, `memory_limit => 512M => 512M` com ele). Validado em homolog com login real. Validado em
**produção com dados reais**: `/pacotes` carregou sem 500 via `http://pmed2.4rm.eb.mil.br/pacotes`
logo após o cutover — o cenário exato que motivou o hotfix original funcionou de primeira no
container.

**Pendência derivada:** `memory_limit=512M` é mitigação, não correção — ver P-15 (§6). O
`PacotesController` continua carregando mais dados do que deveria; o limite mais alto só empurra o
problema para um volume de dados maior.

---

### ADR-10 — Egress corporativo para GHCR/Docker Hub precisa de liberação explícita, por ambiente
**Contexto:** o diagnóstico read-only de produção (2026-08-11) mostrou `curl https://ghcr.io/v2/`
travando em timeout, apesar do DNS resolver (`4.228.31.152`) e do `ufw`/iptables do **host** estarem
totalmente abertos (`ACCEPT`). Homolog nunca teve esse problema. Conclusão: o bloqueio era num
firewall **corporativo**, fora do host, não documentado em nenhum diagnóstico anterior — homolog e
produção não compartilham necessariamente a mesma política de egress, mesmo sendo VMs do mesmo
template.

**Decisão:** tratar a confirmação de egress como um gate explícito (F0) antes de qualquer
provisionamento, testado com o método mais forte disponível — não só `curl`, mas `docker pull
hello-world` depois do Docker instalado, porque uma regra pode liberar o `curl` do host e ainda
assim falhar para o caminho de rede usado pelo daemon do Docker.

**Resultado prático (2026-08-11):** solicitação de liberação aberta pelo usuário antes da janela;
confirmada com `401` (não timeout) em `ghcr.io` **e** `registry-1.docker.io` — o pedido cobriu os
dois registries que a stack usa (`ghcr.io/xlipesousa/pmed2` e as imagens públicas `nginx`/`redis` do
Docker Hub), mesmo sem ter sido pedido explicitamente para o Docker Hub. Revalidado com sucesso já
com o Docker instalado (`docker pull hello-world`, seção 2a do script de provisionamento).

**Achado colateral:** o mesmo firewall corporativo só libera as portas **80/443** para o operador via
Teleport — a porta 8080 (onde a stack sobe em paralelo, antes do cutover) nunca foi acessível para
teste manual. Isso forçou adiar a validação de `/pacotes` com dados reais para **logo após** o
cutover, em vez de antes (ver F5/F6 em `planos/ESTADO-MIGRACAO-PROD.md`) — funcionou, mas é uma
lacuna real no isolamento do cutover paralelo (ADR-04) que vale ter em mente numa próxima migração
com o mesmo tipo de restrição de rede.

**Consequência para o futuro:** não presumir que a liberação de rede de um ambiente vale para outro.
Testar cedo, com o método mais forte disponível (`docker pull`, não só `curl`).

---

### ADR-11 — Token do GHCR precisa ser PAT *classic*, não fine-grained
**Contexto:** no 1º disparo real de `cd-prod.yml` (2026-08-11), o `docker login ghcr.io` teve
`Login Succeeded`, mas o `docker compose pull` falhou com `403 Forbidden` em todas as 6 tentativas de
retry: `unexpected status from HEAD request to https://ghcr.io/v2/xlipesousa/pmed2/manifests/v3.0.7:
403 Forbidden`. Diferente do bloqueio de rede da ADR-10 (que dá timeout), aqui a conexão e a
autenticação funcionam — só a autorização para ler aquele pacote específico falha.

**Causa:** o PAT gerado para `PMED2_PROD_GHCR_TOKEN` não era um token *classic* com escopo
`read:packages` (o padrão já estabelecido e funcional em homolog desde `PMED2_HOM_GHCR_TOKEN`).
Tokens fine-grained têm suporte limitado/inconsistente para leitura de pacotes no GHCR — o login pode
até validar as credenciais, mas a operação de pull é negada.

**Decisão:** regenerar sempre como **Tokens (classic)**, escopo `read:packages` apenas — nunca
fine-grained, para qualquer secret `*_GHCR_TOKEN` deste projeto.

**Resultado prático (2026-08-11):** regenerado o token como classic; 2º disparo de `cd-prod.yml`
fechou 100% verde, incluindo o pull da imagem privada. Nenhum outro ajuste foi necessário.

**Consequência para o futuro:** ao gerar qualquer `*_GHCR_TOKEN` novo (rotação inclusa — os tokens
têm validade de 90 dias, ver P-10), confirmar explicitamente "Tokens (classic)" na tela do GitHub, não
"Fine-grained tokens". Um `docker login` bem-sucedido **não** é prova de que o token vai funcionar
para `pull` — só o `pull` de verdade prova isso (ver §3, nova armadilha).

---

## 3. Armadilhas de verificação (por que quase demos por bom o que estava quebrado)

| Armadilha | Por quê | O que usar no lugar |
|---|---|---|
| `/health` retorna 200 | É uma rota que devolve **JSON estático** (`routes/web.php:24`). Não toca no banco. Passa com o DB inacessível. | `php artisan migrate:status` dentro do container |
| `curl http://127.0.0.1/health` durante o cutover paralelo | Na porta 80 quem responde é o **nginx bare-metal**. Falso-positivo: valida o ambiente antigo achando que é o novo. | Bater direto em `127.0.0.1:8080` |
| `curl -I .../login` no workflow | `-I` sem `-f` **não falha** em HTTP 500. | `curl -fsS` + `grep -c csrf-token` |
| Healthcheck logo após `up -d` | Sem retry, dispara o `trap ERR` → **rollback espúrio** mesmo com tudo correto. | Loop com timeout |
| Tag verde no `docker-build` | `docker-build` e `cd-homolog` disparam **em paralelo** na mesma tag; não há `needs`/`workflow_run`. O `pull` pode rodar antes da imagem existir. | Retry no `pull` |
| `docker login` com "Login Succeeded" | Só prova que as credenciais são válidas — **não** prova que o token pode fazer `pull` de um pacote específico. Um PAT fine-grained mal configurado loga OK e falha no `pull` com `403 Forbidden` (ADR-11). | Sempre validar com o `docker compose pull` de verdade, não só o `login` |

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

## 5. Roteiro de produção — **executado com sucesso em 2026-08-11**

Migração de produção para Docker está **funcionalmente completa**: `cd-prod.yml` fecha verde,
6 containers de pé, `/pacotes` com dados reais confirmado sem 500 na URL pública
(`http://pmed2.4rm.eb.mil.br`). Checklist original, com o resultado real de cada item:

- [x] Homologação estável em Docker por um ciclo completo antes de iniciar (v3.0.5→v3.0.7, múltiplos
  deploys verdes)
- [x] ADR-05 decidida explicitamente para produção — **ACL (`setfacl`), não `chown`** (ver ADR-05)
- [x] Janela de manutenção comunicada (T3.8) — provisionamento e cutover feitos na mesma janela
- [x] Backup do banco de produção verificado com `gunzip -t` — dois backups, um manual (1.4M) e um
  automático do próprio script de provisionamento (1.4M), guardados fora de `/var/www`
- [x] Snapshot da VM de produção — "VM Snapshot 11/02/2026, 17:00:50"
- [x] Confirmado `APP_KEY` único em `/var/www/pmed2/shared/.env` de produção (ADR-06)
- [x] Subnet fixa da rede `pmed2` (`10.219.20.0/24`, ADR-08) — criada com sucesso, zero incidentes
- [x] Rede `pmed2-prod-net` criada explicitamente ANTES do MySQL — confirmado, sem crash-loop
- [x] `extra_hosts` com IP fixo do gateway (`10.219.20.1`), nunca `host-gateway` — confirmado
- [x] Secret `PMED2_PROD_SSH_KNOWN_HOSTS` já existia desde 2026-02-27/08-08, funcionou de primeira
- [x] Usuário SSH de deploy (`admin21ct`) adicionado ao grupo `docker` — incorporado *no próprio
  script* de provisionamento (P4.2), não descoberto ao vivo como aconteceu em homolog
- [x] Secrets `PMED2_PROD_GHCR_USER`/`PMED2_PROD_GHCR_TOKEN` criados antes do disparo — **mas o 1º
  token gerado era do tipo errado** (fine-grained em vez de classic), causou `403 Forbidden` no
  1º disparo real; corrigido regenerando como classic (ver ADR-11)
- [x] **Item novo, não previsto no checklist original:** egress corporativo para `ghcr.io` +
  Docker Hub precisou de liberação de firewall explícita (ADR-10) — bloqueava com timeout antes da
  liberação, algo que homolog nunca teve

**Diferença notável frente a homolog:** o provisionamento (F4, o script de infraestrutura) fechou
**100% verde na 1ª tentativa** — zero incidentes de rede/MySQL, porque as 3 lições de homolog (grupo
docker, revalidação de egress, e a própria sequência rede-antes-do-MySQL da ADR-08) foram
incorporadas *no script*, não descobertas ao vivo de novo. Os dois problemas reais desta migração
(GHCR 403, e a decisão pendente da ADR-05) eram coisas que homolog **não tinha** para ensinar —
apareceram só em produção, e agora estão documentados aqui para a próxima vez.

Diferenças de execução em relação ao homolog (F0→F6, `homolog`→`prod`):
1. `cd-prod.yml` é `workflow_dispatch` com environment `production` protegido por required reviewer
   (`xlipesousa`) — cada disparo pausou de fato até aprovação manual pelo GitHub, confirmado ao vivo
   nos dois disparos desta migração.
2. `cd-prod.yml` tem `mode: rollback` + `rollback_tag`, que homolog não tem — **ainda não testado**
   (não foi necessário; primeiro deploy não teve `previous_tag`). Testar antes do próximo deploy real.
3. O firewall corporativo do operador só libera 80/443 via Teleport — a validação de `/pacotes` com
   dados reais (P5.6) precisou ser adiada para logo **depois** do cutover, não antes (diferente de
   homolog, que testou pela 8080 antes de virar o tráfego). Funcionou, mas é uma lacuna real no
   isolamento do cutover paralelo (ver ADR-04/ADR-10) a considerar numa próxima migração.

---

## 6. Pendências herdadas (fora do escopo desta migração, mas registradas)

| # | Pendência | Gravidade |
|---|---|---|
| P-1 | **Homolog e produção têm as mesmas chaves de host SSH** (mesmo `ssh-rsa`, `ecdsa`, `ed25519`) — indício de VMs clonadas de um template com as chaves já geradas, em vez de cada uma gerar as suas no primeiro boot. Enfraquece o pinning de `known_hosts`. | Alta |
| P-2 | O valor de `PMED2_HOM_SSH_HOST` **não é** o nome de exibição do Teleport. `known_hosts` hasheado é calculado sobre a string exata da conexão — por isso escanear pelo Teleport gerou hashes que nunca casavam. Regenerar **sempre a partir do runner**. | Média (resolvida, mas repetível) |
| P-3 | Sem TLS em homologação **nem produção** (HTTP puro), sem certbot instalado em nenhum dos dois. Produção é acessada por IP (`10.122.8.15`) e por um DNS interno (`pmed2.4rm.eb.mil.br`) sem certificado. | Média |
| P-4 | `ufw` inativo e iptables ACCEPT em homologação **e produção** (nível de host). Produção tem, adicionalmente, um firewall corporativo separado controlando egress/acesso do operador — ver ADR-10. | Média |
| P-5 | `docker-compose.yml` × `docker-compose.retomada.yml` sem canônico definido (ver §4). | Baixa |
| P-6 | ~~`planos/` e `docs/` não são versionados~~ — resolvido em 2026-08-10: este guia passou a ser exceção versionada (§7). Os demais arquivos de `planos/` seguem locais por design. | Resolvida |
| P-7 | Anti-pattern "carrega tabela inteira e filtra 6× no Blade" em `PacotesController::index()` e correlatos; mitigado por seleção de colunas, não resolvido. Causou 500 por memória em produção na v2.1.4. **Mitigado de novo em 2026-08-11 via `memory_limit=512M` na imagem (ADR-09) — segue sem correção real, ver P-15.** | Média |
| P-8 | `git filter-repo` para limpar `.env` do histórico Git — marcado CRÍTICO em `planos/plano-retomada.md`, nunca executado. | Alta |
| P-9 | ~~Usuário SSH de deploy precisa estar no grupo `docker`~~ — resolvido em 2026-08-11: incorporado como passo explícito (`usermod -aG docker`) em `scripts/provisionar-docker-prod.sh`, seção 1a. Rodou sem incidente na 1ª tentativa em produção. | Resolvida |
| P-10 | **Pacote GHCR privado exige secrets `*_GHCR_USER`/`*_GHCR_TOKEN`, sempre PAT classic (não fine-grained)** — resolvido em 2026-08-11 para produção (`PMED2_PROD_GHCR_USER`/`PMED2_PROD_GHCR_TOKEN`), depois de um 1º token gerado como fine-grained causar `403 Forbidden` no pull (ADR-11). **Datas de expiração (90 dias): `PMED2_HOM_GHCR_TOKEN` criado 2026-08-10 → expira ~2026-11-08; `PMED2_PROD_GHCR_TOKEN` criado/regenerado 2026-08-11 → expira ~2026-11-09.** **Verificar a validade (`gh secret list` vs. essas datas) antes de qualquer disparo de `cd-homolog.yml`/`cd-prod.yml`** — um token expirado só falha em pleno meio do deploy (`pull` com `403`/`unauthorized`), depois de outros steps já terem mutado estado no servidor (secret gravado, sessão SSH aberta). | Resolvida (rotação em ~2026-11-08/09) |
| P-11 | ~~Versão exibida na UI (`config/version.php`) hardcoded, desatualizada (3.0.3 vs deploy real 3.0.5)~~ — resolvido em 2026-08-10 (`20952e6c`): passou a ler de `composer.json`. Efeito só aparece no próximo deploy. | Resolvida |
| P-12 | ~~ADR-05 (`user: "0:0"`) segue como dívida técnica não paga~~ — resolvido em 2026-08-11: decisão tomada e implementada nos dois ambientes via ACL (`setfacl`), não `chown` (ver ADR-05). Containers rodam como `www-data` (uid 82), não root. | Resolvida |
| P-13 | Isolamento do cutover paralelo (ADR-04) não cobre mudanças no MySQL do host — o incidente de F2 (loop de crash do MySQL) afetou o bare-metal mesmo com a stack nova ainda não recebendo tráfego, porque os dois ambientes compartilham o mesmo banco. **Em produção o mesmo passo rodou sem incidente** (script já incorporava a lição), mas o risco estrutural (banco compartilhado) permanece — vale o mesmo cuidado numa próxima mudança de host. | Média |
| P-14 | ~~`cd-prod.yml` não recebeu nenhuma das correções aplicadas a `cd-homolog.yml`~~ — resolvido em 2026-08-11 (`027e2fc5`): todas as 11 correções espelhadas (porta 8080, `APP_KEY`, `DB_HOST`, retry de pull/healthcheck, gate `migrate:status`, `rollback` explícito, `known_hosts` isolado, healthcheck pós-deploy com retry, secret via `setfacl`). Validado ao vivo: `cd-prod.yml` fechou 100% verde. | Resolvida |
| P-15 | **`memory_limit=512M` (ADR-09) é mitigação, não correção.** A causa real — `PacotesController::index()` carregando mais dados do que o necessário e filtrando no Blade — segue sem correção. Revisitar em uma versão futura: paginação real no banco, seleção de colunas, ou mover o filtro para SQL. | Média |
| P-16 | `cd-prod.yml` tem `mode: rollback`/`rollback_tag`, que `cd-homolog.yml` não tem, e **nunca foi exercitado de verdade** (o 1º deploy de produção não tinha `previous_tag` para testar contra). Testar explicitamente antes do próximo deploy real de produção — não descobrir se funciona só quando for preciso de fato. | Alta |

---

## 7. Sobre o versionamento deste guia

`planos/` está no `.gitignore` como `/planos/*` (o resto da pasta — plano de execução, estado vivo da
migração — permanece local, por decisão do commit `e9d8c0db`).

**Decisão (2026-08-10):** este arquivo é a exceção — `!/planos/guia-decisoes-producao.md` — e passa a
ser versionado, acompanhando o repositório. Não contém segredos, apenas decisões técnicas e seus
motivos, então não há risco em publicá-lo. Os demais arquivos de `planos/` (plano de execução, estado
da migração tarefa a tarefa) seguem locais, por não terem valor de consulta futura fora desta migração
pontual.
