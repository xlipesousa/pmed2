---
title: Ações por equipe
tags:
  - dominio
  - core
  - seguranca
---

# Ações por equipe

O modelo mental de linha de montagem — "cada equipe age enquanto o pacote está com ela, e
depois passa adiante" — é a regra geral. **Mas tem exceções deliberadas, e elas são a parte
mais importante desta nota.**

## Regra geral

Cada equipe age sobre o pacote enquanto `localizacao_atual` é a dela. Depois de mover, a
equipe perde toda ação sobre o pacote exceto visualizar o histórico. Em `mover()`:

```php
} else if ($pacote->localizacao_atual == 'lisura' && $user->role === 'lisura') {
```

`admin` pode executar qualquer ação, usando a mesma lógica da equipe em que o pacote está.
`auditor` só visualiza.

## As três exceções

> [!important] Estas quebram o modelo "uma equipe por localização"
> Não são bugs — duas são regra de negócio deliberada. A terceira é uma verificação que
> ficou frouxa.

### 1. Protocolo age sobre pacote localizado em Glosa

Quando `estado_glosa == 'Aguardando Recurso de Glosa'`, a ação **"Recebimento de Recurso de
Glosa"** fica disponível para a equipe **Protocolo**, mesmo com o pacote em `glosa`.

**A razão de negócio.** Glosar é *retirar* valores considerados indevidos de uma fatura.
Esse ato dá à OCS/PSA o direito de questionar a retirada, pelo instrumento do **recurso**.
Isso abre uma janela de expectativa: o sistema fica esperando um documento que pode vir ou
não.

E **o Protocolo é a única entrada do fluxo** — não há outra porta pela qual um documento
externo entre no sistema. Então a espera acontece lá, ainda que a responsabilidade pelo
pacote continue sendo da Glosa.

A janela fecha de duas formas, e **as duas devolvem o controle à Glosa**:

| Evento | Quem registra | Resultado |
|---|---|---|
| O recurso chega | Protocolo, via "Recebimento de Recurso de Glosa" | `estado_glosa → Recurso recebido`. Glosa passa a poder deferir ou indeferir |
| O prazo se esgota | Glosa, via "Recurso não recebido" | `estado_glosa → Recurso não recebido`. O pacote segue para `arquivo` ou `sire` |

Implementado em `registrarRecebimentoRecurso()` (`PacotesController.php:1163`), que verifica
papel e `estado_glosa` — **e deliberadamente não verifica `localizacao_atual`**.

> [!warning] O fluxograma original não deixa a volta clara
> Ele desenha a ida (a aresta de "Recurso?" para Protocolo) mas não a devolução à Glosa.
> Ver [[Fluxograma original]] — a nota registra a correção.

> [!danger] Esta é a etapa que gerou o [[Bug do protocolo]]
> Registrar o recurso como **um novo pacote**, em vez de usar a ação no pacote existente,
> trava o pacote original em `Aguardando Recurso de Glosa` para sempre e polui a base com
> um pacote que não é pacote. Aconteceu em produção. Há um bloqueio na interface desde
> então, mas os registros ruins anteriores continuam lá.

### 2. SIRE age sobre pacote localizado em Glosa

Enquanto houver `valor_pendente > 0`, as ações **"Aguardando Limite de Crédito"** e
**"Informar Pagamento"** continuam disponíveis para o SIRE, mesmo com o pacote em `glosa`.

A razão: a contestação da glosa e o pagamento do que já é incontroverso correm em paralelo.
Travar o pagamento até a glosa resolver atrasaria dinheiro que não está em disputa.

`registrarAguardandoLimite()` implementa isso explicitamente:

```php
if (!($user->role === 'admin' || ($user->role === 'sire'
    && in_array($pacote->localizacao_atual, ['sire', 'glosa'])))) {
```

### 3. `registrarPagamento` não verifica localização nenhuma

> [!bug] Divergência com a especificação
> A especificação original exige que "Informar Pagamento" só esteja disponível quando a
> localização for **SIRE ou Glosa**. `registrarPagamento()` (`PacotesController.php:879`)
> verifica apenas o papel:
>
> ```php
> if (!Auth::user()->isAdmin() && !Auth::user()->hasRole('sire')) {
> ```
>
> Na prática: um usuário `sire` pode registrar pagamento em pacote que já está em `arquivo`
> ou `arquivado`. Rastreado em [[P-18]].

## Catálogo de ações

### Protocolo

| Ação | Efeito | Condição |
|---|---|---|
| Criar Novo Pacote | Cria o pacote, `estado_geral=Normal`, `estado_glosa=Não identificada` | — |
| Editar | Todos os campos | `localizacao_atual == protocolo` |
| Mover | → Lisura (destino obrigatório) | `localizacao_atual == protocolo` |
| **Recebimento de Recurso de Glosa** | `estado_glosa → Recurso recebido` | `estado_glosa == Aguardando Recurso de Glosa`, **qualquer localização** |

### Lisura

| Ação | Efeito | Condição |
|---|---|---|
| Editar | `valor_fatura`, tipo de conta, `valor_glosa` | `localizacao_atual == lisura` |
| Mover | → SIRE | `localizacao_atual == lisura` |

Inserir valor de glosa muda `estado_glosa` para `Glosa Identificada`. A glosa não pode
exceder o valor da fatura.

### SIRE

| Ação | Efeito | Condição |
|---|---|---|
| Informar Pagamento | Soma em `valor_pago`, subtrai de `valor_pendente` | `valor_pendente > 0` (localização **não** verificada — ver acima) |
| Aguardando Limite de Crédito | `estado_geral → Aguardando Limite de Crédito` | `valor_pendente > 0`, localização em `sire` ou `glosa` |
| Mover | Ramificação de 7 casos | `localizacao_atual == sire` |

A ramificação do `mover()` a partir do SIRE está detalhada em [[Ciclo de vida do pacote]].

### Glosa

Ordem **obrigatória e travada** — enquanto a anterior não rodar, nenhuma outra fica disponível:

| # | Ação | Efeito |
|---|---|---|
| 1 | Notificação de Existência de Glosa | `estado_glosa → Existência de Glosa Notificada`. Registra como a OCS/PSA foi avisada. Inicia o prazo de retirada |
| 2 | Retirada de Ofício de Glosa | `estado_glosa → Ofício de Glosa Retirado`. **Dispara automaticamente a ação 3** |
| 3 | *(automática)* Aguardando Recurso de Glosa | `estado_glosa → Aguardando Recurso de Glosa`. Reconfigura quem pode agir |

Depois do estado `Aguardando Recurso de Glosa`, a equipe Glosa fica com **uma única ação
disponível**:

| Ação | Efeito |
|---|---|
| Recurso não recebido | → `arquivo` se `valor_pendente == 0`; → `sire` se `> 0` |

E, quando o Protocolo registra o recebimento do recurso, abrem-se duas:

| Ação | Efeito |
|---|---|
| Recurso Indeferido | → `arquivo` se `valor_pendente == 0`; → `sire` se `> 0` |
| Recurso Deferido | → `sire` sempre. **Soma `valor_deferido` em `valor_pendente`** |

> [!warning] "Recurso deferido" aumenta o valor devido
> É a única ação do sistema que **incrementa** `valor_pendente`. Deferir um recurso
> significa reconhecer que a glosa foi indevida — o valor volta a ser devido à OCS/PSA.
> Quem ler o código esperando que `valor_pendente` só diminua vai se surpreender.
>
> Na implementação atual as duas ações são **um único endpoint** (`analisarRecurso`,
> `PacotesController.php:1293`) com um parâmetro `resultado in:deferido,indeferido` — a
> especificação original as descrevia como ações separadas. Diferença de forma, não de
> comportamento.

### Arquivo

| Ação | Efeito | Condição |
|---|---|---|
| Arquivar | `localizacao_atual → arquivado`, `estado_geral → Arquivado` | `localizacao_atual == arquivo`. Uma vez por pacote |
| Editar localização física | Campo descritivo de onde o pacote está guardado | localização em `arquivo` ou `arquivado` |

## O que a especificação previa e não existe

> [!bug] Ações em lote nunca foram implementadas
> A especificação original descreve três, todas com requisito de **atomicidade** (se um
> pacote do lote não puder ser movido, nenhum é movido):
>
> - **Mover em Lote** — checkbox na datatable, para todas as equipes exceto Arquivo
> - **Arquivar em Lote** — para a equipe Arquivo
> - **Mover Arquivo em Lote** — altera a localização física de vários pacotes de uma vez
>
> Nenhuma existe no código: não há rota, controller nem view. Rastreado em [[P-19]].

Ver [[Estados do pacote]] e [[Fluxograma original]].
