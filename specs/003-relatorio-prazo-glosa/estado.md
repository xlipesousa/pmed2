# Estado — Spec 003: Relatório de prazo de recurso vencido

> **Regra de ouro: nenhuma tarefa marcada 🟢 sem evidência colada.**

Legenda: 🔴 pendente · 🟡 em andamento · 🟢 concluído

Verificação executada em 2026-08-13 contra a stack Docker local (`docker-compose.yml`,
imagem `pmed2-local:dev` rebuildada com o código novo — `app` não usa bind-mount, então
todo rebuild foi necessário para refletir mudanças). Autenticação via HTTP direto
(`curl` com sessão/cookie, CSRF real) contra `http://localhost:8080`, dados de teste criados
via `tinker` e removidos ao final.

## Pré-requisito

Confirmado: `T1.1` (regra de 30 dias corridos a partir de `data_retirada_oficio`) foi
assumida a partir de D-2/D-3 da spec, sem confirmação síncrona do cliente — sinalizado
como suposição razoável, não bloqueante para a execução.

## F1 — O relatório

### T1.2 — Controller, rota e view

| | |
|---|---|
| **Status** | 🟢 |
| **Executor** | claude |
| **Ação** | `RelatorioController::prazoRecurso`, scope `Pacote::prazoRecursoVencido()`, rota `relatorios/prazo-recurso`, view `relatorios/prazo-recurso.blade.php`, card em `relatorios/index.blade.php` |
| **Critério** | Pacote com 45 dias aparece; um de 10 dias não; um anulado não |
| **Evidência** | 3 pacotes de teste criados via tinker (45d/10d/anulado-45d). `Pacote::validos()->prazoRecursoVencido()->pluck('numero_fatura')` retornou **apenas** `TESTE-P23-45D`. Requisição HTTP real (`GET /relatorios/prazo-recurso`, sessão autenticada) → `HTTP 200`, corpo contém `TESTE-P23-45D` (1×), não contém `TESTE-P23-10D` nem `TESTE-P23-ANULADO`. Card "1 pacote(s) com prazo vencido". |
| **Achado corrigido durante a verificação** | `diasDesdeRetiradaOficio()` inicialmente retornava float (`45.00152993125 dias`) — Carbon 3 não trunca mais `diffInDays()` por padrão. Corrigido com `(int)` no model, rebuild, reverificado: exibe "45 dias". |
| **Rollback** | Reverter migration não se aplica (sem migration nesta fase); reverter os arquivos do commit |

## F2 — Ação direta

### T2.1 — Botão "Registrar recurso não recebido" no relatório

| | |
|---|---|
| **Status** | 🟢 |
| **Executor** | claude |
| **Critério** | A partir do relatório, registrar a ação; o pacote sai da lista e vai para `arquivo` ou `sire` conforme o valor pendente |
| **Evidência** | `POST /pacotes/182/recurso-nao-recebido` com CSRF real → `HTTP 302`. Estado antes: `localizacao=glosa, estado_glosa=Aguardando Recurso de Glosa`. Depois: `localizacao=sire` (`valor_pendente=200 > 0`, regra confirmada), `estado_glosa=Recurso não recebido`. Pacote confirmadamente **ausente** do relatório após a ação (`grep -c TESTE-P23-45D` → 0). |

## F3 — Visibilidade *(fecha parte de P-23)*

### T3.1 — Contador no dashboard e destaque na aba Glosa

| | |
|---|---|
| **Status** | 🟢 |
| **Executor** | claude |
| **Critério** | O número aparece no dashboard e bate com a quantidade de linhas do relatório; a linha do pacote vencido é destacada na aba Glosa |
| **Evidência** | `GET /dashboard` autenticado → card "Prazo de recurso vencido" com `<h3>1</h3>`, batendo com o relatório. |
| **Achado corrigido durante a verificação (2 bugs reais, não hipotéticos)** | **(1)** `PacotesController::index()` seleciona colunas explicitamente (mitigação de P-7/P-15) e **não incluía `data_retirada_oficio`** — o destaque avaliava sempre falso porque o campo chegava `null` na view. Corrigido acrescentando a coluna ao `select()`. **(2)** A mesma checagem não excluía pacotes `anulado` (diferente do scope usado no relatório/dashboard). Corrigido acrescentando `anulado` ao `select()` e à condição. Confirmado por HTTP real: antes da correção, 0 destaques onde deveria haver 1; depois, exatamente 1 destaque, no pacote correto (185, não-anulado), e **nenhum** no anulado (184). |

## F4 — Ancorar a sugestão de prazo à data de notificação

### T4.1 — Corrigir a âncora da sugestão (de "hoje" para "data de notificação")

| | |
|---|---|
| **Status** | 🟡 |
| **Executor** | claude |
| **Ação** | `resources/views/pacotes/ver.blade.php`: listener em `change.datetimepicker` de `#data_notificacao_div`, recalcula `data_limite_retirada` como `data_notificacao + 30 dias`, só enquanto o campo de prazo não tiver sido editado manualmente (flag `data('editadoManualmente')`, resetada a cada abertura do modal) |
| **Achado que motivou esta fase** | A sugestão **já existia** (`hoje + 30 dias`), mas ancorada em "hoje" — corrigido em `docs/40-decisoes/P-23.md` |
| **Verificado** | Código presente e servido (`grep -c editadoManualmente` no HTML de `/pacotes/185` → 3 ocorrências). Nome do evento (`change.datetimepicker`) e payload (`e.date`, pode ser `false`) conferidos contra a API documentada do tempusdominus-bootstrap-4 v5.39.0 (versão confirmada via CDN carregado nas views irmãs) — biblioteca usada em todo o formulário, mesmo padrão de namespace de evento. |
| **Não verificado interativamente** | Não foi possível abrir o modal num navegador real nesta sessão (as ferramentas de preview não conseguem assumir a stack Docker já em execução sem derrubá-la). A lógica foi validada por leitura de código e API, não por interação real com o datepicker. |
| **Rollback** | Reverter o bloco de JS adicionado ao fim de `ver.blade.php` |

> [!WARNING] Fechar T4.1 de fato
> Antes de considerar F4 encerrada: abrir `/pacotes/{id}` num navegador, abrir o modal de
> notificação de glosa, escolher uma data de notificação retroativa, e confirmar visualmente
> que o campo de prazo atualiza para `data_notificacao + 30 dias`.

---

## Log de ocorrências

| Data | O que aconteceu | Resolução |
|---|---|---|
| 2026-08-13 | `app` roda de imagem baked sem bind-mount; edições no disco não refletiam no container | Rebuild explícito (`docker compose up -d --build app`) a cada mudança de código PHP/Blade antes de verificar |
| 2026-08-13 | `diasDesdeRetiradaOficio()` retornava float por mudança de comportamento do Carbon 3 (`diffInDays()` não trunca mais) | `(int)` cast no model |
| 2026-08-13 | Destaque na aba Glosa sempre falso: `data_retirada_oficio` fora do `select()` explícito de `PacotesController::index()` (mitigação P-7/P-15) | Coluna acrescentada ao whitelist |
| 2026-08-13 | Destaque na aba Glosa não excluía pacotes anulados | `anulado` acrescentado ao `select()` e à condição, espelhando `Pacote::validos()` |
| 2026-08-13 | Preview tools não conseguiram assumir a stack Docker já em execução (porta 8080 ocupada) | Verificação via `curl` autenticado (CSRF + cookie de sessão) contra a stack real, em vez de navegador |
