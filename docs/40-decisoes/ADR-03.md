---
title: "ADR-03 — Adotar Redis para cache e fila"
tags:
  - decisao
  - adr
status: confirmada
data: 2026-08-10
ambiente: homolog, producao
---

# ADR-03 — Adotar Redis para cache e fila

## Contexto

Os composes já traziam `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis` e um service `redis`.
O ambiente bare-metal rodava `file`/`file`/`database` e **não tinha worker de fila nem
scheduler** — sem cron nem systemd unit, ninguém consumia a fila e ninguém rodava o backup.

## Decisão

Manter o compose como escrito e adotar Redis nos dois ambientes.

## Razão

O ganho não é o Redis em si — é passar a **ter** os serviços `queue` e `scheduler` rodando.
`app/Console/Kernel.php` agenda `scripts/backup.sh` diariamente; sem o container `scheduler`,
esse agendamento nunca executava. Foi exatamente isso que deixou produção **5 meses sem
backup** antes da migração.

## Efeito colateral aceito

Sessões são reiniciadas no corte (usuários deslogam). Aceitável em homologação; em produção
foi comunicado antes da janela.

## Resultado

`scheduler` e `queue` confirmados de pé e saudáveis nos dois ambientes. O backup diário voltou
a rodar automaticamente — encerra o histórico de 5 meses sem backup.

Ver [[Stack e ambientes]].
