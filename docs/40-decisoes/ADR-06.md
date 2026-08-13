---
title: "ADR-06 — APP_KEY propagado do servidor"
tags:
  - decisao
  - adr
  - seguranca
status: confirmada
data: 2026-08-10
ambiente: homolog, producao
---

# ADR-06 — `APP_KEY` é propagado a partir do `shared/.env` do servidor

## Contexto

Nenhum compose de homolog/produção passava `APP_KEY`, e a imagem não contém `.env`
(excluído pelo `.dockerignore`). O app subiria sem chave de criptografia estável.

## Decisão

O workflow lê `APP_KEY` do `shared/.env` do servidor e propaga ao compose.

## Razão crítica

> [!danger] Nunca gerar uma chave nova
> Tem que ser **a chave que já existe**. Gerar uma nova invalida todas as sessões e torna
> ilegível qualquer dado encriptado persistido no banco (casts `encrypted`, tokens, etc.).

## Gate obrigatório

Antes de disparar qualquer deploy: confirmar
`grep -c '^APP_KEY=base64:' /var/www/pmed2/shared/.env` = 1. Se faltar, **parar** — nunca
gerar chave nova.

## Resultado

Confirmado presente e único em todas as verificações, nos dois ambientes. Sessões e dados
encriptados preservados através da migração — nenhuma queixa de logout forçado ou dado
ilegível após o cutover.

Ver [[Runbook de deploy]].
