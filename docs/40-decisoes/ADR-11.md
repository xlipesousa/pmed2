---
title: "ADR-11 — Token do GHCR deve ser classic"
tags:
  - decisao
  - adr
status: confirmada
data: 2026-08-11
ambiente: producao
---

# ADR-11 — Token do GHCR precisa ser PAT *classic*, não fine-grained

## Contexto

No 1º disparo real de `cd-prod.yml`, `docker login ghcr.io` teve "Login Succeeded", mas
`docker compose pull` falhou com `403 Forbidden` em todas as 6 tentativas de retry.
Diferente do bloqueio de rede da [[ADR-10]] (que dá timeout), aqui a conexão e a
autenticação funcionam — só a autorização para ler aquele pacote específico falha.

## Causa

O PAT gerado não era um token *classic* com escopo `read:packages` — era **fine-grained**.
Tokens fine-grained têm suporte limitado/inconsistente para leitura de pacotes no GHCR: o
login pode validar as credenciais, mas o pull é negado.

## Decisão

Regenerar sempre como **Tokens (classic)**, escopo `read:packages` apenas — nunca
fine-grained, para qualquer secret `*_GHCR_TOKEN` deste projeto.

## Resultado

Regenerado como classic; 2º disparo de `cd-prod.yml` fechou 100% verde, incluindo o pull da
imagem privada.

> [!danger] `docker login` bem-sucedido não é prova de que o `pull` vai funcionar
> Só um `pull` de verdade prova isso. Ao gerar ou rotacionar qualquer `*_GHCR_TOKEN` (os
> tokens têm validade de 90 dias — ver [[Pendências|P-10]]), confirmar explicitamente
> "Tokens (classic)" na tela do GitHub, nunca "Fine-grained tokens".

Ver [[Runbook de deploy]].
