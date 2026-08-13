---
title: "ADR-05 — Secret lido pelo container sem rodar como root"
tags:
  - decisao
  - adr
  - seguranca
status: resolvida
data: 2026-08-11
ambiente: homolog, producao
---

# ADR-05 — `user: "0:0"` nos containers que leem o secret *(dívida paga)*

## Contexto

O workflow grava `shared/secrets/db_password` com `chmod 600`, dono do usuário SSH de deploy
(uid 1000). O container roda como `www-data` da imagem Alpine — **uid 82**. Nenhum dos dois
lê um arquivo `600` do outro.

## Decisão original (assumida como dívida)

Rodar `app`/`queue`/`scheduler` com `user: "0:0"` (root), mesma solução já usada no dev
local. Funcionou sem incidente em homologação — mas rodar PHP-FPM como root em produção é
indesejável.

## Dívida paga (2026-08-11)

**`chown 82:82` foi descartado ao vivo**: exige privilégio de root para trocar o *dono* de
um arquivo, e o usuário SSH de deploy **não tem sudo sem senha em produção** — confirmado
por teste direto (`sudo -n true` pediu senha). Inviável num step SSH não-interativo.

**Solução real: `setfacl -m u:82:r`.** ACL resolve o mesmo problema sem precisar de root —
é uma operação normal do **dono** do arquivo (o usuário SSH que já grava o secret),
concedendo leitura ao uid 82 sem trocar a posse. Só a instalação do pacote `acl`, uma vez,
na provisão, precisa de sudo — e essa etapa já é interativa por natureza.

## Resultado

Implementado em `cd-homolog.yml` e `cd-prod.yml` (step "Instalar segredo DB por arquivo"),
validado com `getfacl`: `user::rw-`, `user:82:r--`, `mask::r--`. `user: "0:0"` removido de
`app`/`queue`/`scheduler` nos dois composes — os containers rodam como `www-data` (uid 82),
não root, nos dois ambientes.

Ver [[Stack e ambientes]].
