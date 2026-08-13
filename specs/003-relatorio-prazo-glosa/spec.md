# Spec 003 — Relatório de prazo de recurso vencido

Pedido do cliente. **A spec de menor custo e maior retorno imediato do backlog** — não
depende da rede de testes nem da spec 001.

## Contexto

### O pedido

Um relatório que apresente as faturas com **mais de 30 dias após a confirmação da retirada
do Ofício de Glosa**.

O cliente explicou o uso: isso facilita registrar a não-entrada do recurso no Protocolo e a
consequente movimentação do pacote para o Arquivo.

### Por que este relatório é exatamente o que falta

Ele é a implementação concreta da metade que `docs/40-decisoes/ADR-12.md` prometeu e nunca
foi entregue.

A ADR-12 decidiu: **o prazo avisa, nunca dispara ação automática** — porque a equipe é
pequena demais para o volume, e automatizar transformaria atraso interno em perda de direito
da OCS/PSA. A metade "não age" está implementada. A metade "avisa" não existe
(`docs/40-decisoes/P-23.md`): hoje só há a tela `pacotes.prazos`, que alguém precisa lembrar
de abrir.

Este relatório é o aviso. E o formato pedido pelo cliente **respeita a ADR-12**: ele lista
para que um humano decida e execute a ação, não fecha a janela sozinho.

### O papel no fluxo

Quando o prazo esgota sem recurso, a ação **"Recurso não recebido"** é executada pela equipe
Glosa e move o pacote para `arquivo` (se `valor_pendente == 0`) ou `sire` (se `> 0`) — ver
`docs/10-dominio/Ações por equipe.md`.

Sem o relatório, ninguém sabe quando essa hora chegou. Pacotes ficam em
`Aguardando Recurso de Glosa` indefinidamente por esquecimento.

> [!IMPORTANTE] Isto se confunde com o bug do protocolo
> Um pacote parado em `Aguardando Recurso de Glosa` pode estar assim por **duas causas
> distintas**: o [[bug do protocolo]] (o recurso chegou mas foi cadastrado como pacote novo)
> ou simples esquecimento (o prazo venceu e ninguém agiu).
>
> Este relatório torna a segunda causa visível — e por isso **ajuda a isolar a primeira**.
> É argumento para fazê-lo cedo, mesmo com a spec 001 em andamento.

## Decisões

| # | Decisão | Razão | Alternativa descartada |
|---|---|---|---|
| D-1 | Relatório listando, **sem** ação automática | `docs/40-decisoes/ADR-12.md`: o prazo avisa, não age | Job que executa "Recurso não recebido" ao vencer |
| D-2 | Contar a partir de `data_retirada_oficio`, não de `data_notificacao_glosa` | É o que o cliente pediu, e é o marco correto: o prazo de recurso começa quando a OCS/PSA retira o ofício | Contar da notificação |
| D-3 | Prazo configurável, default 30 dias | O número veio do processo, mas pode mudar. Deixar fixo no código repetiria o problema de `brasil@123` | `30` literal no query |
| D-4 | Ação direta a partir do relatório | O relatório existe para provocar uma ação; obrigar a procurar o pacote depois desperdiça o ganho | Relatório apenas informativo |

## Fases

### F1 — O relatório

Segue o padrão dos cinco relatórios existentes em `RelatorioController` (`statusPacotes`,
`performance`, `glosas`, `financeiro`, `ocspsa`).

| Passo | O quê |
|---|---|
| 1 | `RelatorioController::prazoRecurso(Request $request)` |
| 2 | Rota `relatorios/prazo-recurso` no grupo existente (`routes/web.php:129`) |
| 3 | View `resources/views/relatorios/prazo-recurso.blade.php` |
| 4 | Card na página índice de relatórios |

**Critério de seleção:**

```sql
estado_glosa = 'Aguardando Recurso de Glosa'
AND data_retirada_oficio IS NOT NULL
AND data_retirada_oficio < (CURDATE() - INTERVAL :dias DAY)
AND (anulado IS NULL OR anulado = 0)
```

**Colunas:** pacote, fatura, OCS/PSA, data de retirada do ofício, **dias decorridos**, valor
da glosa, valor pendente.

Ordenação padrão: mais vencido primeiro — é a fila de trabalho.

**Critério de aceite:** um pacote com `data_retirada_oficio` de 45 dias atrás e estado
`Aguardando Recurso de Glosa` aparece, com "45 dias" na coluna. Um de 10 dias não aparece.
Um anulado não aparece.

> [!NOTA] Reaproveitar o que existe
> `Pacote` já tem `prazoRetiradaExcedido()` e `diasRetiradaRestantes()`. **Eles usam
> `data_limite_retirada`**, que é o prazo *de retirada do ofício* — marco anterior ao deste
> relatório. Não confundir: aqui o marco é `data_retirada_oficio` (D-2). Se a lógica for
> parecida, extrair um predicado novo em vez de sobrecarregar os existentes.

### F2 — Ação direta a partir do relatório

Cada linha oferece **"Registrar recurso não recebido"**, que leva à ação existente no pacote
(`registrarRecursoNaoRecebido`) — respeitando as permissões já vigentes (equipe Glosa ou
`admin`).

**Critério de aceite:** a partir do relatório, registrar "recurso não recebido" em um pacote;
ele sai do relatório e vai para `arquivo` ou `sire` conforme o valor pendente.

### F3 — Tornar o aviso visível *(fecha P-23)*

O relatório só ajuda quem lembra de abri-lo. Para fechar `docs/40-decisoes/P-23.md`:

| Passo | O quê |
|---|---|
| 1 | Contador no dashboard: "N pacotes com prazo de recurso vencido", com link |
| 2 | Destaque visual na aba Glosa da listagem de pacotes |
| 3 | *(opcional)* Resumo periódico via `scheduler`, que já está de pé desde a migração Docker (`docs/40-decisoes/ADR-03.md`) |

**Critério de aceite:** o número aparece no dashboard sem que ninguém precise procurar.

### F4 — Sugerir a data limite na notificação *(melhoria correlata)*

Hoje `data_limite_retirada` é **digitada em branco** pelo operador
(`docs/10-dominio/Glosa, recurso e prazos.md`) — o sistema não sugere nada, e um erro de
digitação não é detectado.

Passar a sugerir `data_notificacao + 30 dias` (editável) é **aviso, não ação** — compatível
com a ADR-12 — e reduz erro na origem.

**Critério de aceite:** ao informar a data de notificação, a data limite é pré-preenchida e
continua editável.

## Riscos

| Risco | Mitigação |
|---|---|
| Interpretado como autorização para automatizar a ação | D-1 é explícita; a ADR-12 está linkada no código do relatório |
| Consulta pesada | Filtro por `estado_glosa` reduz muito o conjunto; avaliar índice em `(estado_glosa, data_retirada_oficio)` se necessário |
| Relatório encher de casos do bug do protocolo | Esperado e **útil** — é o sinal que ajuda a isolar as duas causas. Anotar isso na view |

## Ordem

**Pode ser feita a qualquer momento**, inclusive em paralelo com a spec 001. Não altera
`store()`, não migra dado, e não depende da base estar limpa.

É a candidata natural para ser **a primeira spec executada pela convenção** — escopo pequeno,
padrão já existente para seguir, e retorno imediato para o cliente.

## Verificação end-to-end

1. Pacote com ofício retirado há 45 dias aparece no relatório com "45 dias".
2. A partir do relatório, registrar "recurso não recebido"; o pacote sai da lista e vai para
   `arquivo` ou `sire` conforme o valor pendente.
3. O contador do dashboard bate com a quantidade de linhas do relatório.
4. O prazo é configurável: mudar para 45 altera o conjunto retornado.
