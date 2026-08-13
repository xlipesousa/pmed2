---
title: "ADR-08 — MySQL na subnet Docker fixa"
tags:
  - decisao
  - adr
status: confirmada
data: 2026-08-11
ambiente: homolog, producao
---

# ADR-08 — MySQL escuta na subnet Docker fixa do compose, com grant restrito a ela

A decisão mais complexa da migração — três correções sucessivas até funcionar.

## Contexto

`DB_HOST=127.0.0.1` no `shared/.env` aponta, dentro de um container, para o **próprio
container**. O MySQL do host só escutava em loopback, com grants só para
`pmed2user@127.0.0.1`/`@localhost`.

## Tentativa 1 (errada): gateway da bridge padrão

Descobrir o gateway via `ip -4 addr show docker0` e configurar `bind-address`/grant com
base nele. **Errado**: `docker0` é a bridge *padrão*; o compose declara uma rede **custom**
própria, com subnet própria atribuída dinamicamente na primeira subida. Um gate de
conectividade testado com `docker run` solto (sem `--network`) cairia na bridge padrão por
default — **passaria mesmo com o grant errado**, mascarando o problema até o primeiro deploy
real.

## Tentativa 2 (insuficiente sozinha): subnet fixa via `ipam.config`

Fixar a subnet no compose (`networks.pmed2.ipam.config.subnet`), deixando o Docker criar a
rede na primeira subida. **Ainda quebrou**: o MySQL não sobe com `bind-address` apontando
para um IP que não existe em nenhuma interface — e a interface só é criada quando a rede é
criada. Como o provisionamento (que configura o MySQL) roda **antes** do primeiro deploy
(que é quando o compose criaria a rede), o MySQL sempre seria configurado cedo demais.

## Correção final: rede externa e persistente, criada explicitamente e cedo

```bash
docker network create --subnet=10.219.20.0/24 pmed2-prod-net   # produção
docker network create --subnet=10.219.10.0/24 pmed2-homolog-net # homolog
```

Isso cria a interface bridge e o gateway imediatamente, mesmo sem nenhum container rodando —
**antes** de o MySQL ser configurado. Os composes referenciam essa rede como
`external: true`, então o `docker compose up` **reaproveita** a rede já criada em vez de
tentar criar a sua própria.

> [!important] Regra geral
> Sempre que uma configuração de host depende de um IP de rede Docker, a rede precisa ser
> criada explicitamente e cedo — nunca deixar para o compose criar implicitamente na hora
> do deploy.

## Correção adicional: `host.docker.internal:host-gateway` não funciona com rede custom

`--add-host=host.docker.internal:host-gateway` resolve para o gateway da bridge **padrão**
do Docker, não para o gateway da rede custom a que o container está anexado — mesmo
passando `--network` explicitamente. Confirmado ao vivo: `getent hosts
host.docker.internal` dentro de um container na rede custom resolvia para `172.17.0.1`,
onde nada escuta.

**Correção**: apontar direto para o IP fixo conhecido —
`extra_hosts: ["host.docker.internal:10.219.20.1"]`, nunca `host-gateway`.

## Resultado em produção

A sequência "rede externa antes do MySQL" funcionou **sem nenhum incidente na 1ª
tentativa** — diferença marcante frente aos 2 incidentes reais de homologação. Confirma que
lições capturadas *no script*, em vez de descobertas ao vivo, eliminam o incidente por
completo.

Ver [[Stack e ambientes]] e [[Modelo de dados]].
