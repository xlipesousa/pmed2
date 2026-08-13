---
title: "ADR-09 — memory_limit explícito na imagem"
tags:
  - decisao
  - adr
status: confirmada — mitigacao
data: 2026-08-11
ambiente: homolog, producao
---

# ADR-09 — `memory_limit` explícito na imagem, via `docker/php/pmed2.ini`

## Contexto

A imagem base `php:8.3-fpm-alpine` não ativa nenhum `php.ini` por padrão. Sem isso, a
aplicação roda com o default compilado do PHP: `memory_limit=128M`. O php-fpm bare-metal de
produção tinha sido ajustado em hotfix para `512M` depois de `PacotesController::index()`
causar erro 500 por estouro de memória, na v2.1.4.

## Decisão

`docker/php/pmed2.ini` com `memory_limit = 512M` explícito, copiado para
`/usr/local/etc/php/conf.d/` no estágio `runtime` do `Dockerfile`.

## Razão

512M não foi escolhido às cegas — é o mesmo valor já validado ao vivo em produção pelo
hotfix anterior. Deixar isso implícito reintroduziria exatamente o bug que o hotfix já tinha
corrigido, só que silenciosamente, na migração para container.

## Resultado

Validado com dados reais em produção: `/pacotes` carregou sem 500 logo após o cutover — o
cenário exato que motivou o hotfix original funcionou de primeira no container.

> [!bug] Isto é mitigação, não correção
> `PacotesController::index()` continua carregando mais dados do que deveria; o limite mais
> alto só empurra o problema para um volume de dados maior. Ver [[Pendências|P-15]] e
> [[Dívida técnica]].

Ver [[Ciclo de vida do pacote]].
