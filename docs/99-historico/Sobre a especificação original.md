---
title: Sobre a especificação original
tags:
  - historico
---

# Sobre a especificação original

[[Especificação original (diário de bordo)]] é o documento que o autor usou para especificar
o PMED 2.0 antes de construí-lo — o "diário de bordo" do projeto.

> [!warning] Não é fonte da verdade sobre o estado atual
> Muita coisa mudou desde então. O documento descreve o sistema **como foi planejado**, não
> como está. Onde ele e o código divergirem, **o código está certo**.
>
> O valor dele é outro: ele responde **"por quê"** em pontos onde o código só mostra o
> "o quê". Regras de negócio cuja motivação não é dedutível da implementação estão lá, e em
> nenhum outro lugar.

## O que foi extraído dele para o cofre

O conhecimento que ainda vale foi incorporado às notas de domínio, com verificação contra o
código:

| Conhecimento | Onde está agora |
|---|---|
| Máquina de 9 estados da glosa, com ordem obrigatória e gatilho automático | [[Estados do pacote]] |
| Catálogo de ações por equipe, com as 3 exceções ao modelo "uma equipe por localização" | [[Ações por equipe]] |
| Razão de negócio do "Aguardando Limite de Crédito" | [[Estados do pacote]] |
| Por que o PMED2 existe (o SIRE não tem API) | [[Visão geral]] |
| Invariante dos mapas de pagamento | [[Mapas de pagamento]] |
| Prazos de 30 dias, e o fato de nunca terem virado regra automática | [[Glosa, recurso e prazos]] |
| Ações em lote especificadas e nunca implementadas | [[P-19]] |

## Imprecisão do próprio documento

> [!warning] O glossário original erra a cardinalidade
> O documento define o pacote como contendo "uma ou mais **faturas** de procedimentos". Está
> errado. A cadeia real é:
>
> **Pacote (1) → Fatura (1) → Guia (1..N)**
>
> É uma fatura por pacote; são as **guias** que são múltiplas. A imprecisão sobreviveu
> porque a guia nunca foi implementada — a confusão nunca produziu erro observável.
>
> O próprio documento é consistente com a versão correta em outro trecho: ao explicar o
> SIRE, ele diz que "o ciclo de vida da **guia** se inicia e termina" naquele sistema.
>
> Ver [[Glossário]] e [[P-24]].

## Divergências encontradas na conferência

A leitura do documento contra o código revelou coisas que ninguém sabia:

- **`estado_glosa` não tem restrição no banco** e os valores gravados não batem com o enum
  declarado na migration — [[P-17]]
- **`registrarPagamento` não verifica localização**, embora a especificação exija SIRE ou
  Glosa — [[P-18]]
- **Nenhuma das três ações em lote existe** — [[P-19]]

Nenhuma dessas seria encontrada lendo só o código, porque o código *parece* coerente
consigo mesmo. Foram encontradas porque havia um documento dizendo o que deveria ser.

## O que deliberadamente não foi extraído

A segunda metade do documento é caderno de trabalho operacional: comandos de `tinker` para
corrigir pacotes na mão, `TRUNCATE` para zerar tabelas, snippets de recuperação de banco,
credenciais de teste dos usuários de cada equipe.

Isso **não** foi para o cofre. É procedimento de emergência de uma época em que não havia
ambiente de homologação nem CI/CD — hoje há os dois, e mexer em produção por `tinker` é
exatamente o que os runbooks existem para evitar. Ver [[Runbook de deploy]] e
[[Runbook de rollback]].

> [!danger] Não copie os comandos daquele documento para produção
> Os blocos de `UPDATE`/`TRUNCATE`/`DROP DATABASE` lá dentro foram escritos para um ambiente
> de desenvolvimento descartável. Rodá-los em produção destrói dado real, e a trilha de
> auditoria ([[Anulação e auditoria]]) não protege contra escrita direta no banco.

## Valor residual

Vale reler o original quando:

- houver dúvida sobre **por que** uma regra existe;
- for preciso decidir se um comportamento estranho é bug ou intenção;
- alguém propuser "simplificar" uma das exceções de [[Ações por equipe]] — todas as três
  têm justificativa de negócio documentada lá.
