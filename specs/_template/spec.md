# Spec NNN — <título>

## Contexto

Por que esta mudança existe. O problema concreto, não a solução. Se veio de uma pendência ou
ADR, linkar: `docs/40-decisoes/P-XX.md`.

Se a mudança toca regra de negócio, linkar a nota de domínio correspondente em
`docs/10-dominio/` — e **ler antes de escrever a spec**, especialmente
`Ações por equipe.md`, cujas exceções são fáceis de quebrar sem perceber.

## Decisões

Cada escolha não óbvia, com o porquê e o que foi descartado.

| # | Decisão | Razão | Alternativa descartada |
|---|---|---|---|
| D-1 | | | |

Decisão com valor de consulta futura além desta mudança vira ADR em `docs/40-decisoes/`.

## Pré-requisitos

- [ ] Teste de caracterização cobrindo o comportamento atual **passa** (obrigatório se a spec
      toca `app/`)
- [ ] …

## Fases

### F1 — <nome>

**Ação:**

**Critério de aceite:** objetivo e testável. Não "melhorar X", mas "`/pacotes` responde em
menos de 500ms com 10k linhas, sem erro".

### F2 — <nome>

…

## Riscos

| Risco | Mitigação |
|---|---|
| | |

## Verificação end-to-end

Como provar, ao final, que a mudança inteira funciona — não fase a fase, mas o conjunto.
