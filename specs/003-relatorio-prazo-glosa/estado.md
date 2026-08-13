# Estado — Spec 003: Relatório de prazo de recurso vencido

> **Regra de ouro: nenhuma tarefa marcada 🟢 sem evidência colada.**

Legenda: 🔴 pendente · 🟡 em andamento · 🟢 concluído

## F1 — O relatório

### T1.1 — Confirmar o prazo com o cliente

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | usuário |
| **Ação** | Confirmar: 30 dias corridos a partir de `data_retirada_oficio`? Dias úteis ou corridos? |
| **Critério** | Regra de contagem confirmada por escrito |
| **Evidência** | *(resposta do cliente)* |

### T1.2 — Controller, rota e view

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | claude |
| **Pré-condição** | T1.1 🟢 |
| **Ação** | `RelatorioController::prazoRecurso`, rota `relatorios/prazo-recurso`, view, card no índice |
| **Critério** | Pacote com ofício retirado há 45 dias aparece com "45 dias"; um de 10 dias não aparece; anulado não aparece |
| **Evidência** | *(saída do relatório com os 3 casos)* |
| **Rollback** | Reverter o commit — feature isolada, sem migration |

---

## F2 — Ação direta

### T2.1 — Botão "Registrar recurso não recebido" no relatório

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | claude |
| **Pré-condição** | T1.2 🟢 |
| **Critério** | A partir do relatório, registrar a ação; o pacote sai da lista e vai para `arquivo` ou `sire` conforme o valor pendente |
| **Evidência** | *(estado do pacote antes/depois)* |

---

## F3 — Visibilidade *(fecha P-23)*

### T3.1 — Contador no dashboard e destaque na aba Glosa

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | claude |
| **Critério** | O número aparece no dashboard e bate com a quantidade de linhas do relatório |
| **Evidência** | *(screenshot + contagem)* |

---

## F4 — Sugerir a data limite na notificação

### T4.1 — Pré-preencher `data_limite_retirada`

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | claude |
| **Critério** | Ao informar a data de notificação, a data limite é sugerida e continua editável |
| **Evidência** | *(screenshot do formulário)* |

---

## Log de ocorrências

| Data | O que aconteceu | Resolução |
|---|---|---|
| | | |
