---
title: "ADR-07 — Segredos por arquivo"
tags:
  - decisao
  - adr
  - seguranca
status: confirmada
data: 2026-08-10
ambiente: homolog, producao
---

# ADR-07 — Segredos por arquivo, não por variável de ambiente

## Contexto

Decisão herdada de antes desta migração: `config/database.php` resolve `DB_PASSWORD` ou, em
fallback, lê `DB_PASSWORD_FILE`. `docker/entrypoint.sh` faz o mesmo.

## Decisão

Manter. Segredo grava em arquivo com permissão restrita, não em variável de ambiente do
compose.

## Incidente real que reforçou a regra

> [!danger] Um fragmento de senha vazou em log de CI antes desta migração
> O workflow interpolava `${{ secrets.*_DB_PASSWORD }}` **direto no corpo de um `run:`**.
> Como a senha continha `$`, o bash tentou expandi-la e abortou com `unbound variable`,
> revelando o fragmento. A máscara do GitHub não protegeu porque só redige o valor
> **completo**, não um fragmento resultante de expansão parcial.

## Regra derivada — vale para qualquer secret, em qualquer workflow

**Nunca interpolar secret dentro do corpo de um `run:`.** Sempre passar via bloco `env:` do
step e referenciar como `"${VAR}"`. Corrigido nos dois workflows de CD.

## Resultado

A regra se provou necessária de novo durante esta própria migração — todos os steps novos
(backup, grants, login no GHCR) seguiram o padrão `env:` desde o início, e nenhum novo
vazamento ocorreu, apesar de várias senhas e tokens novos terem sido manipulados.

Ver [[Stack e ambientes]].
