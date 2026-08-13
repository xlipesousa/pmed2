---
title: Fluxograma original
tags:
  - dominio
  - core
---

# Fluxograma original

O fluxograma desenhado durante a especificação do sistema, em draw.io. É o único artefato
que mostra o fluxo **inteiro** numa peça só, incluindo os retornos que a descrição textual
deixa implícitos.

![[pmed2.drawio.svg]]

Arquivos: `docs/diagram/pmed2.drawio.svg` (editável no draw.io) e `docs/diagram/pmed2.json`
(estrutura legível por máquina — nós e arestas, útil para verificar o grafo sem abrir o SVG).

## Por que ele importa mais do que parece

A leitura ingênua do fluxo é uma fila:

```
Protocolo → Lisura → SIRE → Glosa → Arquivo → Arquivado
```

O fluxograma mostra que isso é falso em três pontos, e todos os três são regra de negócio
real, não desenho impreciso:

### 1. O SIRE é um losango, não uma estação

De `SIRE` saem duas arestas: para `Glosa` e para `Pagamento`. Qual delas depende de
`valor_glosa`, `valor_recurso_glosa`, `valor_pendente` e `localizacao_anterior` — os 7 casos
descritos em [[Ciclo de vida do pacote]].

### 2. Há um retorno de "Recurso?" para Protocolo

A aresta mais fácil de perder no diagrama, e a mais importante: de `Recurso ?` sai uma seta
de volta para o **Protocolo**. Não é o pacote voltando fisicamente — é a ação
**"Recebimento de Recurso de Glosa"** ficando disponível para a equipe Protocolo enquanto o
pacote continua localizado em Glosa.

É a exceção nº 1 documentada em [[Ações por equipe]]. Sem esta aresta, o modelo "uma equipe
por localização" parece verdadeiro — e leva a escrever autorização errada.

### 3. "Valor Pendente?" é um laço, não uma passagem

De `Pagamento` sai para `Valor Pendente?`, que volta para `Pagamento` enquanto houver saldo,
e só sai para `Arquivo` quando zera. É o pagamento parcial: uma fatura é quitada em várias
parcelas conforme o limite de crédito libera.

Esse laço é a razão de existirem os [[Mapas de pagamento]] — cada volta do laço pode ser um
mapa diferente.

## Correspondência com o modelo atual

O fluxograma foi desenhado antes da implementação. O que ele chama de "Pagamento" virou o
conjunto de ações do SIRE (`registrarPagamento`), não uma localização própria — não há
`localizacao_atual == 'pagamento'`. As anotações laterais do diagrama
("Aguardando Limite de Crédito", "Aguardando recurso de Glosa") são valores de
`estado_geral` e `estado_glosa`, não localizações. Ver [[Estados do pacote]].

Fora isso, o desenho continua correto — inclusive nos retornos, que é onde ele ganha da
descrição textual.

## Como editar

O MCP do draw.io está disponível nesta configuração e edita tanto XML quanto Mermaid. Regra
do projeto ([[Convenções da documentação]]):

- **Mermaid dentro das notas** para tudo que precisa versionar e dar diff — é o que se usa
  em [[Ciclo de vida do pacote]], [[Estados do pacote]] e [[Modelo de dados]].
- **draw.io** apenas para este fluxograma, que é peça de apresentação e tem valor histórico
  como registro da especificação.

Se o fluxo de negócio mudar, atualize os dois — o Mermaid nas notas e este SVG.

Ver [[Especificação original (diário de bordo)]].
