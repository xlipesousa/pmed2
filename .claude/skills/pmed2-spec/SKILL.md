---
name: pmed2-spec
description: Convenção do PMED2 para planejar e executar mudanças estruturais (refatoração, nova feature de peso, mudança de infraestrutura) usando specs em specs/ com gate de evidência. Use ao iniciar qualquer mudança não-trivial neste repositório, ou quando o usuário pedir "crie uma spec", "vamos planejar", "documente essa mudança antes de fazer".
---

# pmed2-spec

Formaliza o método que já funcionou na migração Docker deste projeto (provisionamento de
produção fechou 10/10 na primeira tentativa porque cada lição foi escrita **antes**, não
redescoberta ao vivo). Não é OpenSpec nem outra ferramenta — é convenção in-repo, leve.

## Quando abrir uma spec

Mudança estrutural: refatoração de um controller, extração de camada de serviço, mudança de
autorização, mudança de schema, mudança de infraestrutura. **Não** é necessário para um typo,
uma correção de uma linha, ou uma mudança cujo blast radius é óbvio e reversível em segundos.

## Estrutura

```
specs/<NNN>-<slug>/
├── spec.md       ← Contexto · Decisões · Fases com critério de aceite · Riscos
└── estado.md     ← Status/Executor/Ação/Critério/Evidência/Rollback, por tarefa
```

`NNN` é sequencial, três dígitos. `slug` é curto e descreve o escopo (`004-index-paginado`,
não `004-melhorias`).

## `spec.md`

- **Contexto** — por que esta mudança, o que a motivou (link para a pendência ou ADR em
  `docs/40-decisoes/`, se houver).
- **Decisões** — cada escolha não óbvia, com o porquê. Vira ADR em
  `docs/40-decisoes/` se tiver valor de consulta futura além desta mudança.
- **Fases**, cada uma com **critério de aceite objetivo e testável** — não "melhorar X",
  mas "`/pacotes` responde em <500ms com 10k linhas e sem erro".
- **Riscos** — o que pode dar errado e a mitigação.

## `estado.md`

Uma tabela por fase, no formato:

| Campo | Preenchimento |
|---|---|
| Status | 🔴 pendente / 🟡 em andamento / 🟢 concluído |
| Executor | quem roda (agente ou humano — mudanças que tocam produção real, se houver, exigem humano) |
| Ação | comando ou passo exato |
| Critério | o que prova que funcionou |
| Evidência | saída real colada — **não um resumo** |
| Rollback | como desfazer, se aplicável |

> [!IMPORTANTE] Regra de ouro
> **Nenhuma tarefa marcada como 🟢 concluída sem evidência colada.** Foi exatamente essa
> disciplina que produziu zero incidentes no provisionamento de produção. "Deveria ter
> funcionado" não é evidência — rodar e colar a saída real é.

## Rede de segurança antes de mexer em código de aplicação

Specs que tocam `app/` **exigem** teste de caracterização passando antes de a Fase 1
começar — este projeto teve, até 2026-08, uma suíte de testes que reportava verde sem
executar nada (ver `docs/20-arquitetura/Dívida técnica.md`). Se a rede de testes real ainda
não cobre o fluxo que a spec vai tocar, a primeira fase da spec é escrever esse teste, antes
de qualquer refatoração.

## Ao terminar

1. `estado.md` com todas as fases 🟢 e evidência.
2. Se a spec revelou uma decisão de arquitetura durável, escrever a ADR em
   `docs/40-decisoes/` e linkar de `docs/40-decisoes/ADRs.md`.
3. Se a spec fechou uma pendência conhecida, atualizar `docs/40-decisoes/Pendências.md`.
4. `specs/<NNN>-<slug>/` permanece no repositório como registro — não apagar depois.
