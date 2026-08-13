---
title: "ADR-12 — Prazo de 30 dias avisa, não age"
tags:
  - decisao
  - adr
status: confirmada
data: 2026-08-12
ambiente: produto
---

# ADR-12 — O prazo de 30 dias é aviso, nunca gatilho automático

## Contexto

O processo de glosa tem dois prazos de 30 dias: um para a OCS/PSA retirar o Ofício de Glosa
depois de notificada, outro para entrar com o recurso depois de retirar o ofício
([[Glosa, recurso e prazos]]).

A [[Sobre a especificação original|especificação original]] registrou o controle desses
prazos como problema em aberto — "pensar em uma forma de controlar e informar/controlar esse
prazo no sistema". Nunca foi resolvido no código: `data_limite_retirada` é digitada à mão e
nada acontece quando ela vence.

## Decisão

**Manter assim, por decisão do cliente.** O prazo é informativo. O sistema deve *avisar* que
venceu; **nenhuma ação é disparada automaticamente**.

## Razão

A equipe é pequena demais para o volume de pacotes que processa. Um gatilho automático — que
movesse o pacote, mudasse estado ou encerrasse a janela de recurso ao bater o prazo — tomaria
decisões de negócio irreversíveis sem que ninguém tivesse olhado o caso.

Com equipe reduzida e fila grande, a chance de um pacote passar do prazo por falta de braço,
e não por inércia da OCS/PSA, é real. Automatizar transformaria atraso interno em perda de
direito da outra parte.

A ação de encerrar a janela continua sendo humana e explícita: "Recurso não recebido",
executada pela equipe Glosa.

## Consequências

- `data_limite_retirada` permanece de entrada manual. Não somar 30 dias automaticamente
  seria inconsistente com esta decisão? Não: **calcular a data sugerida é aceitável**;
  o que a decisão proíbe é *agir* sobre ela.
- A tela de prazos (`pacotes.prazos`) e os predicados `prazoRetiradaExcedido()` /
  `diasRetiradaRestantes()` são o mecanismo de aviso.

> [!bug] O aviso previsto por esta decisão não existe de fato
> A decisão é "avisa mas não age". Hoje o sistema **não avisa** — não há notificação, alerta
> no dashboard nem destaque de pacote vencido; só a consulta manual da tela de prazos, que
> exige que alguém lembre de olhar.
>
> Ou seja: a metade "não age" está implementada, a metade "avisa" não. Rastreado em [[P-23]].

Ver [[Glosa, recurso e prazos]] e [[Estados do pacote]].
