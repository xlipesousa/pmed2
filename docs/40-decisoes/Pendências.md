---
title: Pendências
tags:
  - pendencia
  - moc
---

# Pendências

O que se sabe que está errado e ainda não foi corrigido, mais o registro do que já foi
resolvido — para não se perguntar "isso já foi tratado?" de novo. Ver a view filtrável em
[[Pendências.base]].

Fonte original: `planos/guia-decisoes-producao.md` §6.

## Abertas

| # | Pendência | Severidade |
|---|---|---|
| [[P-01\|P-1]] | Homolog e produção compartilham chaves de host SSH | Alta |
| [[P-03\|P-3]] | Sem TLS em nenhum dos dois ambientes | Média |
| [[P-04\|P-4]] | `ufw` inativo, iptables ACCEPT nos dois hosts | Média |
| [[P-05\|P-5]] | Dois composes locais concorrentes, sem canônico | Baixa |
| [[P-07\|P-7]] | Anti-pattern de carregamento em `PacotesController::index()` | Média |
| [[P-08\|P-8]] | `.env` ainda no histórico do Git — **crítica** | Alta |
| [[P-13\|P-13]] | Cutover paralelo não isola o MySQL compartilhado | Média |
| [[P-15\|P-15]] | `memory_limit=512M` é mitigação, não correção | Média |
| [[P-16\|P-16]] | Rollback de `cd-prod.yml` nunca testado de verdade | Alta |
| [[P-17\|P-17]] | `estado_glosa`/`localizacao_atual` sem restrição no banco | Alta |
| [[P-18\|P-18]] | `registrarPagamento` não verifica localização | Média |
| [[P-19\|P-19]] | Ações em lote especificadas e nunca implementadas | Baixa |
| [[P-20\|P-20]] | Senha de reset fixa (`brasil@123`) no código, repo público | Alta |
| [[P-21\|P-21]] | **Recursos cadastrados como pacotes em produção** — em investigação | Alta |
| [[P-22\|P-22]] | Sem índice único em `(numero_fatura, ocs_psa_id)` | Média |
| [[P-23\|P-23]] | O aviso de prazo vencido nunca foi implementado | Média |
| [[P-24\|P-24]] | A entidade "guia" não existe no modelo | Média |
| [[P-25\|P-25]] | Carbon 3 `diffInDays()` retorna float, sem truncar | Baixa |

> [!danger] P-21 é a de maior urgência do inventário
> É a única cujo dano está acontecendo **agora**, em produção, e cresce com o tempo. Ver
> [[Bug do protocolo]] e a spec de investigação em `specs/001-caca-bug-protocolo/`.

> [!info] P-17 a P-20 vieram da conferência com a especificação original
> Foram encontradas comparando o código com o [[Sobre a especificação original|diário de
> bordo]] do projeto — nenhuma seria detectada lendo só o código, porque o código parece
> coerente consigo mesmo.
>
> P-21 a P-23 vieram do conhecimento operacional do autor: coisas que aconteceram na
> prática e que nem o código nem a especificação registravam.

## Resolvidas

| # | Pendência | Resolução |
|---|---|---|
| P-2 | `known_hosts` calculado sobre string errada | Regenerar sempre a partir do runner — resolvida, mas repetível se esquecida |
| P-6 | `planos/` e `docs/` não versionados | Resolvida em 2026-08-10 (guia) e 2026-08-12 (este cofre) |
| P-9 | Usuário SSH de deploy fora do grupo `docker` | Incorporado ao script de provisionamento |
| [[P-10\|P-10]] | GHCR exige PAT classic, expira em 90 dias | Resolvida — requer rotação periódica, ver a nota |
| P-11 | Versão da UI hardcoded e desatualizada | Passou a ler de `composer.json` |
| P-12 | ADR-05 (`user: "0:0"`) como dívida não paga | Paga via ACL — ver [[ADR-05]] |
| P-14 | `cd-prod.yml` sem as correções de `cd-homolog.yml` | Todas as 11 espelhadas |

## Como usar

Ao resolver uma pendência: mudar `status` no frontmatter da nota individual para
`resolvida`, mover a linha da tabela "Abertas" para "Resolvidas" aqui, e descrever a
resolução em uma frase.
