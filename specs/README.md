# Specs

Convenção do PMED 2.0 para planejar e executar mudanças estruturais. Formaliza o método que
já funcionou na migração Docker deste projeto — o provisionamento de produção fechou 10/10 na
primeira tentativa porque cada lição foi escrita **antes**, não redescoberta ao vivo.

Não é OpenSpec nem outra ferramenta. É convenção in-repo, leve, sem dependência.

## Estrutura

```
specs/<NNN>-<slug>/
├── spec.md       ← Contexto · Decisões · Fases com critério de aceite · Riscos
└── estado.md     ← Status/Executor/Ação/Critério/Evidência/Rollback, por tarefa
```

Modelo em [`_template/`](_template/). `NNN` é sequencial de três dígitos; `slug` descreve o
escopo (`004-index-paginado`, não `004-melhorias`).

## Regra de ouro

> **Nenhuma tarefa marcada como concluída sem evidência colada.**

Saída real de comando, não resumo. "Deveria ter funcionado" não é evidência.

## Quando abrir uma spec

Mudança estrutural: refatoração de controller, extração de camada de serviço, mudança de
autorização, mudança de schema, mudança de infraestrutura.

**Não** é necessário para typo, correção de uma linha, ou mudança cujo raio de impacto é
óbvio e reversível em segundos.

## Pré-requisito para specs que tocam `app/`

Teste de caracterização passando **antes** da Fase 1. Este projeto teve uma suíte que
reportava verde sem executar nada (ver `docs/20-arquitetura/Dívida técnica.md`). Se a rede
de testes ainda não cobre o fluxo que a spec vai tocar, escrever esse teste é a primeira
fase da spec.

## Specs ativas

| # | Spec | Estado | Nota |
|---|---|---|---|
| 001 | [Caça ao bug do protocolo](001-caca-bug-protocolo/spec.md) | 🔴 não iniciada | **Prioridade máxima** — afeta produção agora |
| 002 | [Rastreamento de guias](002-rastreamento-guias/spec.md) | 🔴 não iniciada | Pedido do cliente. Depende da 001 e da rede de testes |
| 003 | [Relatório de prazo de recurso](003-relatorio-prazo-glosa/spec.md) | 🟡 F1-F3 verificadas end-to-end, F4 implementada sem verificação interativa | Pedido do cliente. Primeira spec executada pela convenção |

**003 executada em 2026-08-13.** Verificação real contra a stack Docker local (HTTP
autenticado, dados de teste, rebuild de imagem a cada mudança) — não apenas leitura de
código. Revelou 3 bugs reais no próprio trabalho antes de qualquer usuário encontrá-los:
Carbon 3 retornando float em `diffInDays()` ([[P-25]] no cofre), e duas colunas ausentes no
`select()` explícito de `PacotesController::index()` que faziam o destaque visual falhar em
silêncio. Ver `specs/003-relatorio-prazo-glosa/estado.md` para o detalhe completo — inclusive
o que ainda falta (verificação interativa do JS de F4 num navegador real).

**Ordem sugerida: 003 → 001 → 002.**

- **003 primeiro** por ser a de menor custo e maior retorno imediato: não altera `store()`,
  não migra dado, segue um padrão que já existe (5 relatórios), e entrega valor ao cliente
  rapidamente. É a candidata natural para estrear a convenção de specs.
  Bônus: ela torna visível a causa "prazo esquecido", ajudando a **isolar** os casos do bug
  do protocolo que a 001 vai caçar.
- **001 antes da 002** porque a 002 altera `PacotesController::store()` — o mesmo método que
  carrega o bloqueio do bug do protocolo — e porque migrar dados enquanto a base está
  corrompida propaga o problema.

## Backlog conhecido

Cada item abaixo já tem contexto suficiente documentado para virar uma spec. A ordem é a do
plano de normalização (`planos/plano-normalizacao-pmed2.md`) — os dois primeiros blocos são
pré-requisito dos demais.

### Rede de segurança (bloqueia todo o resto)

| Escopo | Fonte |
|---|---|
| `TestCase` com `RefreshDatabase`, remover os `markTestSkipped` | `docs/20-arquitetura/Dívida técnica.md` §1 |
| Pint + Larastan + `composer audit` bloqueantes no CI | idem |
| Factories para os 13 models | idem |
| Testes de caracterização do ciclo do pacote, glosa, anulação, mapas | `docs/10-dominio/` |

### Código morto (barato, sem risco)

| Escopo | Fonte |
|---|---|
| `PacoteController.php` (104L), `updateLisura()` (219L), `HomeController`, models `Movimentacao`/`Glosa`, `routes/web.txt`, `PacotesController*.txt` | `docs/20-arquitetura/Dívida técnica.md` §5 |

### Correções de domínio descobertas na conferência com a especificação original

| Escopo | Fonte |
|---|---|
| **Senha de reset aleatória + troca obrigatória** — hoje é fixa e pública | `docs/40-decisoes/P-20.md` |
| Enums PHP para `estado_glosa`/`localizacao_atual`, com `cast` no model | `docs/40-decisoes/P-17.md` |
| Guarda de localização em `registrarPagamento` | `docs/40-decisoes/P-18.md` |
| Ações em lote (Mover, Arquivar, Mover Arquivo) com atomicidade | `docs/40-decisoes/P-19.md` |

> P-20 é a de maior urgência do bloco: é credencial conhecida num repositório público. Não
> depende da rede de testes para ser corrigida. Contexto: foi implementada assim por falta
> de servidor de e-mail — há um caminho de correção que não depende de infraestrutura nova.

### Produto

| Escopo | Fonte |
|---|---|
| Aviso de prazo vencido — dashboard, destaque na listagem, sugestão da data limite. **Sem gatilho de ação** (`docs/40-decisoes/ADR-12.md`) | `docs/40-decisoes/P-23.md` |

> A spec de enums (P-17) e a de autorização (F4.1) se sobrepõem: as duas tocam as
> comparações espalhadas de `localizacao_atual`. Vale planejá-las juntas ou em sequência
> imediata, não em paralelo.

### Autorização

| Escopo | Fonte |
|---|---|
| Policies + Form Requests; fechar `anulacao.ver`; registrar ou remover `CheckRole`; mover as checagens *ad hoc* para a camada de rota | `docs/00-projeto/Perfis e permissões.md`, `docs/10-dominio/Ações por equipe.md` |

> [!IMPORTANTE] As três exceções de `Ações por equipe` são regra de negócio
> Protocolo agindo sobre pacote em Glosa, e SIRE agindo sobre pacote em Glosa, **não são
> bugs**. Uma Policy que assuma "a equipe da localização é a única que age" quebra o fluxo
> de recurso de glosa. Ler `docs/10-dominio/Ações por equipe.md` antes de escrever a Policy.

### Desempenho

| Escopo | Fonte |
|---|---|
| `PacotesController::index()` paginado por aba + query agregada para os contadores | `docs/40-decisoes/P-15.md` |

Critério de aceite objetivo: reduzir `memory_limit` de volta a 128M e `/pacotes` continuar
funcionando com dados reais.

### Camada de serviço e front-end

| Escopo | Fonte |
|---|---|
| Extrair a máquina de estados das 12 transições de `PacotesController` | `docs/10-dominio/Ciclo de vida do pacote.md`, `Ações por equipe.md` |
| Exportações assíncronas via fila (Redis já está de pé, `jobs` vazia) | `docs/20-arquitetura/Modelo de dados.md` |
| Consolidar front-end no Vite (30 scripts CDN, 3 versões de Chart.js) | `docs/20-arquitetura/Dívida técnica.md` §6 |
