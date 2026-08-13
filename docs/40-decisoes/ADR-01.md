---
title: "ADR-01 — MySQL permanece no host"
tags:
  - decisao
  - adr
status: confirmada
data: 2026-08-10
ambiente: homolog, producao
---

# ADR-01 — MySQL permanece no host; não é containerizado

## Contexto

O ambiente de desenvolvimento roda MariaDB em container. Os composes de homolog/produção
(`deploy/compose.*.yml`) **nunca tiveram** service `db` — sempre assumiram `DB_HOST` externo.

## Decisão

Manter o MySQL do host em ambos os ambientes. Não adicionar service `db` aos composes.

## Razão

Dado é estado. Containerizar um banco com dado real é o passo de maior risco da migração e não
era necessário para o objetivo (pipeline funcional). Evita dump/restore, evita trocar
MariaDB↔MySQL 8, e mantém backup/gestão do banco no plano do sistema operacional.

## Custo aceito

O ambiente não é 100% reproduzível/descartável como o dev. Paridade total seria uma migração
própria, separada desta.

## Resultado

Confirmada nos dois ambientes. Zero dump/restore de dados; o MySQL do host só foi tocado em
`bind-address` ([[ADR-08]]) e um novo usuário/grant. Migrations pré-existentes continuaram
`Ran` sem re-execução.

Ver [[Stack e ambientes]].
