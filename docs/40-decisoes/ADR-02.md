---
title: "ADR-02 — nginx do host como proxy reverso"
tags:
  - decisao
  - adr
status: confirmada
data: 2026-08-10
ambiente: homolog, producao
---

# ADR-02 — nginx do host vira proxy reverso; container publica em `127.0.0.1:8080`

## Contexto

O nginx do host ocupa a porta 80. O service `web` do compose publica em
`${APP_HTTP_PORT:-8080}:80` — colisão garantida se ambos tentarem a 80.

## Decisão

O container publica em `127.0.0.1:8080`; o nginx do host faz `proxy_pass` para ele.

## Razão

O cutover vira uma troca de arquivo de configuração do nginx, com rollback em segundos
(restaurar backup + `reload`). O bare-metal nunca precisa parar durante a validação. Mantém
também um ponto único no host para TLS/certbot no futuro ([[Pendências|P-3]], ainda aberta).

**Bind em loopback é deliberado**: `127.0.0.1:8080:80`, não `8080:80`. Como `ufw` está
inativo e o iptables é ACCEPT ([[Pendências|P-4]]), publicar em `0.0.0.0` exporia a aplicação
diretamente na rede, contornando o nginx.

## Resultado

Confirmada. Cutover real levou segundos (`nginx -t` + `reload`); o script de cutover restaura
o backup automaticamente se `nginx -t` falhar — nunca houve risco de deixar o nginx num
estado inválido. Rollback testado e disponível em 1 comando, não precisou ser usado em
nenhum dos dois ambientes.

Ver [[Stack e ambientes]] e [[Runbook de rollback]].
