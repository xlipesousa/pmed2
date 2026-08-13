# Estado — Spec 002: Rastreamento de guias

> **Regra de ouro: nenhuma tarefa marcada 🟢 sem evidência colada.**

Legenda: 🔴 pendente · 🟡 em andamento · 🟢 concluído

## Pré-requisitos

| | Item | Status |
|---|---|---|
| PR-1 | Spec 001 concluída | 🔴 |
| PR-2 | Teste de caracterização cobrindo `store()` — criação de pacote **e** o bloqueio de fatura duplicada | 🔴 |

---

## F1 — Modelo

### T1.1 — Migration e model `Guia`

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | claude |
| **Pré-condição** | PR-1, PR-2 🟢 |
| **Ação** | Migration `guias` (FK cascade, único em `(pacote_id, numero)`, índice em `numero`), model `Guia`, `hasMany` em `Pacote` |
| **Critério** | `$pacote->guias` retorna coleção; `Guia::where('numero',X)->first()->pacote` retorna o pacote |
| **Evidência** | *(saída de `migrate` + tinker das duas relações)* |
| **Rollback** | Migration `down()` |

### T1.2 — Medir o tamanho típico

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | usuário |
| **Ação** | Confirmar com o cliente quantas guias tem um pacote típico e o máximo observado |
| **Critério** | Número conhecido — define se a entrada manual da F2 é viável ou precisa de importação |
| **Evidência** | *(resposta do cliente)* |

---

## F2 — Entrada na criação

### T2.1 — Bloco de guias no formulário

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | claude |
| **Pré-condição** | T1.1 🟢 |
| **Critério** | Criar pacote com 4 guias; as 4 persistem; erro de validação preserva o que foi digitado |
| **Evidência** | *(screenshot + registros no banco)* |
| **Rollback** | Reverter a view e o `store()` |

### T2.2 — Regressão do bloqueio do bug do protocolo

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | claude |
| **Pré-condição** | T2.1 🟢 |
| **Ação** | Rodar o teste de caracterização de PR-2 |
| **Critério** | O bloqueio de `numero_fatura + ocs_psa_id` duplicado continua funcionando |
| **Evidência** | *(saída do teste)* |

---

## F3 — Consulta *(o que o cliente pediu)*

### T3.1 — Guia → pacote (busca reversa)

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | claude |
| **Critério** | Dado um número de guia, chega-se ao pacote e à fatura em uma busca |
| **Evidência** | *(busca real + resultado)* |

### T3.2 — Pacote → guias

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | claude |
| **Critério** | Abrindo o pacote, veem-se todas as suas guias |
| **Evidência** | *(screenshot de `ver.blade.php`)* |

### T3.3 — Validação com o cliente

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | usuário |
| **Pré-condição** | T3.1, T3.2 🟢 |
| **Critério** | O cliente confirma que resolve a necessidade declarada |
| **Evidência** | *(confirmação)* |

---

## F4 — Conciliação *(condicional a D-3)*

### T4.1 — Relatório de divergência `valor_fatura` × `Σ valor_guia`

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | claude |
| **Pré-condição** | Campo `valor` adotado e populado |
| **Critério** | Relatório lista faturas com divergência |
| **Evidência** | *(saída do relatório)* |

---

## Log de ocorrências

| Data | O que aconteceu | Resolução |
|---|---|---|
| | | |
