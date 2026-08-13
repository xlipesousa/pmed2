# Estado — Spec 001: Caça ao bug do protocolo

> **Regra de ouro: nenhuma tarefa marcada 🟢 sem evidência colada.**
> Saída real de comando/query, não resumo.

Legenda: 🔴 pendente · 🟡 em andamento · 🟢 concluído

## F1 — Restaurar backup de produção em ambiente controlado

### T1.1 — Obter e verificar o backup

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | usuário |
| **Ação** | Localizar o backup mais recente em produção (`/var/www/pmed2/backups/`), verificar com `gunzip -t` |
| **Critério** | `gunzip -t` sem erro; arquivo > 1 MB; data recente |
| **Evidência** | *(colar `ls -la` e a saída do `gunzip -t`)* |
| **Rollback** | n/a — somente leitura |

### T1.2 — Restaurar em ambiente isolado

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | usuário |
| **Pré-condição** | T1.1 🟢 |
| **Ação** | Restaurar em homologação e/ou desenvolvimento |
| **Critério** | `SELECT COUNT(*) FROM pacotes` bate com produção; app sobe sem erro |
| **Evidência** | *(colar as duas contagens, lado a lado)* |
| **Rollback** | Descartar a base restaurada |

---

## F2 — Diagnóstico (somente leitura)

### T2.1 — Duplicatas de fatura por OCS/PSA

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | usuário |
| **Pré-condição** | T1.2 🟢 |
| **Ação** | Consulta 1 da spec |
| **Critério** | Lista completa de duplicatas, com localização e estado de glosa de cada |
| **Evidência** | *(colar a saída)* |
| **Rollback** | n/a |

### T2.2 — Pacotes famintos

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | usuário |
| **Ação** | Consulta 2 da spec |
| **Critério** | Lista de pacotes travados em `Aguardando Recurso de Glosa` |
| **Evidência** | *(colar a saída)* |
| **Rollback** | n/a |

### T2.3 — Sinal `tipo_conta = 'Recurso de Glosa'`

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | usuário |
| **Ação** | Consulta 3 da spec |
| **Critério** | Lista de pacotes marcados como recurso |
| **Evidência** | *(colar a saída)* |
| **Rollback** | n/a |

### T2.4 — Cruzar e produzir os pares

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | claude |
| **Pré-condição** | T2.1, T2.2, T2.3 🟢 |
| **Ação** | Cruzar os três conjuntos, produzir a lista de pares `(original travado ↔ recurso)` |
| **Critério** | Número concreto de casos candidatos |
| **Evidência** | *(tabela de pares)* |
| **Rollback** | n/a |

---

## F3 — Classificação

### T3.1 — Classificar cada caso em A / B / C

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | claude + usuário (conhecimento de negócio) |
| **Pré-condição** | T2.4 🟢 |
| **Critério** | Todo caso classificado, sem "não sei" remanescente |
| **Evidência** | *(tabela caso → categoria → justificativa)* |
| **Rollback** | n/a |

---

## F4 — Estratégia de correção e piloto

### T4.1 — Definir a estratégia

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | claude + usuário |
| **Pré-condição** | T3.1 🟢 |
| **Critério** | Decisão registrada: como destravar o original, o que fazer com o pacote-recurso |
| **Evidência** | *(decisão + razão, promovida a ADR se tiver valor durável)* |

### T4.2 — Piloto em um caso, na cópia restaurada

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | usuário |
| **Pré-condição** | T4.1 🟢 |
| **Critério** | Pacote original destravado e andando; pacote-recurso fora das contagens; relatórios de anulação não distorcidos |
| **Evidência** | *(estado antes/depois do pacote + contagens)* |
| **Rollback** | Restaurar a cópia |

---

## F5 — Aplicar em produção

### T5.1 — Backup imediatamente antes

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | usuário |
| **Critério** | Backup íntegro, guardado fora de `/var/www` |
| **Evidência** | *(ls + `gunzip -t`)* |

### T5.2 — Aplicar a correção

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | usuário |
| **Pré-condição** | T4.2 🟢 e T5.1 🟢 |
| **Critério** | Consultas da F2 em produção retornam só categorias B e C |
| **Evidência** | *(saída das consultas pós-correção)* |
| **Rollback** | **Restaurar o backup de T5.1** |

---

## F6 — Fechar o caminho de volta

### T6.1 — Índice de unicidade (P-22)

| | |
|---|---|
| **Status** | 🔴 |
| **Executor** | claude (migration) + usuário (deploy) |
| **Pré-condição** | T5.2 🟢 |
| **Critério** | Inserir duplicata fora de `store()` é rejeitado |
| **Evidência** | *(tentativa de INSERT + erro retornado)* |
| **Rollback** | Migration `down()` |

---

## Log de ocorrências

| Data | O que aconteceu | Resolução |
|---|---|---|
| | | |
