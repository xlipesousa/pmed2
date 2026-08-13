---
title: PMED 2.0 — Documentação
tags:
  - moc
---

# PMED 2.0

Sistema de controle de pagamento de faturas hospitalares do **FUSEx** (plano de saúde do
Exército). Contas a pagar B2B com um pipeline de auditoria no meio: nada é pago sem passar por
conferência e possível contestação.

> [!info] Este cofre é a fonte da verdade
> Documentação escrita à mão, versionada junto com o código. Se o código e uma nota
> divergirem, **o código está certo e a nota é um bug** — corrija a nota.
>
> Não confunda com `graphify-out/`, que é *derivado* do código por extração automática,
> descartável, e não deve ser editado à mão. Ver [[Convenções da documentação]].

## Por onde começar

| Se você é… | Comece por |
|---|---|
| Novo no projeto | [[Visão geral]] → [[Glossário]] → [[Fluxograma original]] |
| Vai mexer no fluxo do pacote | [[Estados do pacote]] → [[Ações por equipe]] |
| Vai mexer no código | [[Stack e ambientes]] → [[Modelo de dados]] → [[Dívida técnica]] |
| Vai fazer deploy | [[Runbook de deploy]] |
| Está com produção quebrada | [[Runbook de rollback]] |

## O projeto

- [[Visão geral]] — o que o sistema faz e para quem
- [[Glossário]] — a cadeia **Pacote → Fatura → Guia**, mais Glosa, Lisura, SIRE, Mapa, OCS/PSA
- [[Perfis e permissões]] — os 8 perfis e como a autorização *realmente* funciona
- [[Convenções da documentação]] — como escrever neste cofre

## Domínio

- [[Fluxograma original]] — o fluxo inteiro numa peça só, **incluindo os retornos**
- [[Ciclo de vida do pacote]] — a ramificação de 7 casos do SIRE
- [[Estados do pacote]] — as 4 dimensões de estado e a máquina de 9 estados da glosa
- [[Ações por equipe]] — o catálogo completo, e as 3 exceções ao "uma equipe por localização"
- [[Glosa, recurso e prazos]] — contestação, recurso e os prazos de retirada
- [[Mapas de pagamento]] — agrupamento de pacotes aprovados para remessa
- [[Anulação e auditoria]] — anulação de pacote e a trilha de auditoria
- [[Bug do protocolo]] — ⚠️ **inconsistência de dados afetando produção agora**

## Arquitetura

- [[Stack e ambientes]] — Laravel 12, Docker, homolog e produção
- [[Modelo de dados]] — tabelas, relações e as **tabelas concorrentes abandonadas**
- [[Mapa de rotas e autorização]] — as 105 rotas e onde a autorização falha
- [[Dívida técnica]] — inventário honesto, com prioridade

## Operação

- [[Runbook de deploy]] — release, tag, homolog, produção
- [[Runbook de rollback]] — quando algo quebra

## Decisões e pendências

- [[ADRs]] — decisões de arquitetura registradas, com o *porquê*
- [[Pendências]] — o que se sabe que está errado e ainda não foi corrigido

## Histórico

- [[Sobre a especificação original]] — o "diário de bordo" do projeto: por que cada regra
  existe. **Não é fonte da verdade sobre o estado atual**, mas responde "por quê" onde o
  código só mostra "o quê"
- [[Histórico]] — índice dos demais documentos de fases encerradas

São **registro**, não instrução: não siga um passo a passo de lá sem confirmar que ainda vale.
