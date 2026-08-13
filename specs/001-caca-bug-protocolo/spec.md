# Spec 001 — Caça ao bug do protocolo

**Prioridade: a mais alta do backlog.** Afeta produção agora, e é a única pendência do
inventário cujo dano cresce a cada dia que passa sem medição.

## Contexto

Recursos de glosa foram cadastrados como **pacotes novos** em vez de registrados no pacote
existente, durante um período em que a interface permitia. O caminho já foi bloqueado em
`PacotesController::store()`, mas **os registros ruins continuam na base de produção**.

Explicação completa do fluxo e da causa: `docs/10-dominio/Bug do protocolo.md`.
Pendências: `docs/40-decisoes/P-21.md` (os dados), `docs/40-decisoes/P-22.md` (a ausência de
constraint que permitiu).

O dano é duplo e silencioso:

- **pacotes glosados travados** em `Aguardando Recurso de Glosa` para sempre, porque o
  registro que os liberaria foi para outro lugar;
- **base poluída** por registros que não são pacotes de fatura, inflando contagens e
  distorcendo valores em relatórios e gráficos.

A extensão é **desconhecida**. A base de desenvolvimento não reflete a de produção.

## Decisões

| # | Decisão | Razão | Alternativa descartada |
|---|---|---|---|
| D-1 | Investigar sobre **cópia restaurada** do backup de produção, nunca consultando produção diretamente | Consulta de diagnóstico em produção é leitura pesada sobre tabela sem índice (P-22), num sistema em uso | Rodar as queries direto em produção — risco de lentidão para os usuários |
| D-2 | **Medir antes de corrigir.** Fase de diagnóstico fecha com número, sem alterar nenhum dado | Não se sabe se são 5 ou 500 casos; a estratégia de correção depende disso | Corrigir caso a caso conforme aparecem |
| D-3 | A correção de dados é **executada pelo usuário**, com backup e rollback prontos | É escrita em dado financeiro real de produção; não é operação de agente | Script automático de correção em massa |
| D-4 | Não criar o índice único (P-22) nesta spec | A base tem violações hoje; a migration falharia. É consequência, não pré-requisito | Criar o índice junto com a correção |

## Pré-requisitos

- [ ] Backup recente de produção disponível e íntegro (`gunzip -t`)
- [ ] Ambiente de destino da restauração definido e isolado

> [!IMPORTANTE] Dado real de saúde
> O backup de produção contém dados reais de faturamento médico. A cópia restaurada em
> desenvolvimento/homologação passa a ser dado sensível: não deve ser compartilhada, deve ser
> descartada ao fim da investigação, e o ambiente que a hospeda não pode estar exposto na
> rede. Ver `docs/40-decisoes/P-03.md` — não há TLS em nenhum dos ambientes.

## Fases

### F1 — Restaurar o backup de produção em ambiente controlado

**Ação:** obter o backup mais recente de produção, verificar integridade, restaurar em
homologação e/ou desenvolvimento.

**Critério de aceite:** `SELECT COUNT(*) FROM pacotes` na cópia bate com a contagem de
produção, e a aplicação sobe contra ela sem erro.

**Executor:** usuário (acesso aos servidores é por Teleport).

### F2 — Diagnóstico: medir a extensão

Somente leitura. Nenhuma escrita nesta fase.

**Ação:** rodar as três consultas de detecção.

**1. Duplicatas de fatura por OCS/PSA** — a assinatura principal:

```sql
SELECT numero_fatura, ocs_psa_id, COUNT(*) AS ocorrencias,
       GROUP_CONCAT(id ORDER BY id) AS pacote_ids,
       GROUP_CONCAT(localizacao_atual ORDER BY id) AS localizacoes,
       GROUP_CONCAT(estado_glosa ORDER BY id) AS estados_glosa
FROM pacotes
WHERE (anulado IS NULL OR anulado = 0)
GROUP BY numero_fatura, ocs_psa_id
HAVING ocorrencias > 1
ORDER BY ocorrencias DESC;
```

**2. Pacotes famintos** — os travados esperando um recurso que foi para outro lugar:

```sql
SELECT id, numero_fatura, ocs_psa_id, data_notificacao_glosa,
       data_limite_retirada, valor_glosa, valor_pendente
FROM pacotes
WHERE estado_glosa = 'Aguardando Recurso de Glosa'
  AND (anulado IS NULL OR anulado = 0)
ORDER BY data_limite_retirada;
```

**3. Sinal complementar** — `Recurso de Glosa` é um `tipo_conta` válido; pacotes marcados
assim podem ser recursos cadastrados como pacote (ou registros legítimos, a conferir):

```sql
SELECT p.id, p.numero_fatura, p.ocs_psa_id, p.localizacao_atual, p.estado_glosa
FROM pacotes p
JOIN tipos_conta tc ON tc.id = p.tipo_conta_id
WHERE tc.nome = 'Recurso de Glosa'
  AND (p.anulado IS NULL OR p.anulado = 0);
```

**Critério de aceite:** um número concreto de casos confirmados, com a lista dos pares
`(pacote original travado ↔ pacote-que-é-recurso)`. Cruzar os resultados 1 e 2 é o que
produz os pares.

**Saída:** tabela de casos em `estado.md`, com evidência colada.

### F3 — Classificar os casos

Nem toda duplicata é o bug. Classificar cada caso encontrado:

| Categoria | O que é | Tratamento |
|---|---|---|
| **A — Bug confirmado** | Duplicata onde o original está travado em `Aguardando Recurso de Glosa` e o segundo registro é o recurso | Corrigir |
| **B — Duplicata por outro motivo** | Erro de digitação, refaturamento legítimo, etc. | Analisar caso a caso |
| **C — Falso positivo** | Mesma fatura legitimamente em dois pacotes | Documentar por que |

**Critério de aceite:** todo caso da F2 classificado, sem "não sei" remanescente.

### F4 — Definir e validar a estratégia de correção

Só depois de F3, porque a estratégia depende do volume e da distribuição das categorias.

Perguntas que F3 responde e F4 decide:

- O pacote original travado deve ser **destravado** registrando o recurso retroativamente
  (com a data real, extraída do pacote-recurso)?
- O pacote-que-é-recurso deve ser **anulado** (usando a trilha de auditoria existente, ver
  `docs/10-dominio/Anulação e auditoria.md`) ou removido?
- Anular preserva a auditoria e é reversível — é a opção mais provável, mas precisa
  confirmar que não distorce os relatórios de anulação.

**Critério de aceite:** a estratégia é aplicada com sucesso em **um caso piloto** na cópia
restaurada, e o resultado é verificado: pacote original destravado e prosseguindo no fluxo,
pacote-recurso fora das contagens.

### F5 — Aplicar em produção

**Executor:** usuário, em janela combinada.

**Pré-condição:** backup de produção **imediatamente antes**, verificado.

**Critério de aceite:** as consultas da F2 rodadas em produção depois da correção retornam
apenas os casos classificados como B e C. Contagem de pacotes e totais financeiros
conferidos contra o valor esperado.

**Rollback:** restaurar o backup da pré-condição.

### F6 — Fechar o caminho de volta

**Ação:** com a base limpa, criar o índice de `docs/40-decisoes/P-22.md` — resolvendo antes
a questão do índice único parcial (pacotes anulados liberam o número da fatura).

**Critério de aceite:** tentativa de inserir duplicata direto no banco (fora de `store()`)
é rejeitada.

## Riscos

| Risco | Mitigação |
|---|---|
| Corrigir um caso que não era o bug | F3 classifica antes de F4 corrigir; F4 valida em piloto |
| Escrita em dado financeiro real | F5 tem backup imediatamente antes e rollback definido; executor humano |
| Dado sensível de saúde numa cópia em dev | Ambiente isolado, cópia descartada ao fim (ver Pré-requisitos) |
| A correção distorce relatórios de anulação | F4 verifica isso explicitamente no piloto |
| Consulta pesada em produção | D-1: investigação roda na cópia, não em produção |

## Verificação end-to-end

1. As consultas de F2, rodadas em produção após F5, retornam zero casos de categoria A.
2. Um pacote que estava travado é acompanhado até `arquivo` ou `sire`, provando que voltou a
   andar.
3. A contagem total de pacotes em produção cai exatamente pelo número de casos corrigidos.
4. Inserir duplicata fora de `store()` passa a falhar (F6).
