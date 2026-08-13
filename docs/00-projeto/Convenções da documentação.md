---
title: Convenções da documentação
tags:
  - projeto
  - meta
---

# Convenções da documentação

## O que é o quê

| | `docs/` (este cofre) | `graphify-out/` | `planos/` |
|---|---|---|---|
| Origem | Escrito à mão | Extraído do código | Escrito à mão |
| Papel | **Fonte da verdade** | Índice de consulta | Plano de execução, efêmero |
| Git | Versionado | Ignorado | Ignorado (1 exceção) |
| Editar à mão | Sim | **Nunca** | Sim |
| Validade | Permanente | Reconstruível | Morre quando o plano acaba |

> [!danger] Nunca rode `graphify --obsidian` neste projeto
> Esse comando gera um cofre Obsidian *próprio*, um arquivo por nó do grafo. Apontado para
> `docs/`, ele **sobrescreve documentação escrita à mão com nós gerados automaticamente**.
>
> Para consultar o grafo use `graphify query "..."`. Para atualizá-lo, `graphify --update`.

## Regras do cofre

1. **Toda nota nova é linkada a partir de [[Home]].** Nota que não aparece no MOC não existe
   na prática — ninguém a encontra.
2. **Wikilinks para notas, markdown para URLs externas.** `[[Modelo de dados]]`, não
   `[Modelo de dados](20-arquitetura/Modelo%20de%20dados.md)`. O Obsidian conserta os links
   sozinho quando uma nota é renomeada; o markdown link não.
3. **Se a nota e o código divergirem, o código está certo.** A nota é o bug. Corrija a nota
   na mesma mudança que revelou a divergência.
4. **Nada de número de versão em prosa.** Escrever "hoje v3.0.3" garante que a nota vai
   envelhecer errada — foi exatamente o que aconteceu com a skill `pmed2-guide`. Aponte para
   `composer.json`, que é a fonte da verdade validada pelo CI.
5. **Diagrama é Mermaid dentro da nota**, não imagem. Versiona, dá diff e pode ser editado
   por qualquer um. A exceção é `diagram/pmed2.drawio.svg`, que é peça de apresentação.
6. **Afirmação técnica precisa de âncora.** Cite o arquivo (e a linha, quando for um ponto
   específico) para que o leitor possa conferir em vez de confiar.

## Estrutura

```
docs/
├── Home.md            ← MOC, ponto de entrada
├── 00-projeto/        ← o que é o sistema, glossário, perfis
├── 10-dominio/        ← as regras de negócio
├── 20-arquitetura/    ← como está construído
├── 30-operacao/       ← runbooks
├── 40-decisoes/       ← ADRs e pendências
├── 99-historico/      ← registro de migrações concluídas
└── diagram/           ← anexos e o .drawio
```

Os prefixos numéricos existem só para ordenar a barra lateral do Obsidian.

## Callouts em uso

| Callout | Quando |
|---|---|
| `> [!info]` | Contexto útil |
| `> [!tip]` | Atalho, jeito melhor de fazer |
| `> [!warning]` | Cuidado necessário, mas não é defeito |
| `> [!bug]` | Defeito conhecido e ainda aberto — sempre linkar [[Pendências]] |
| `> [!danger]` | Ação que causa dano real se feita errado |

## Fluxo de escrita

Documentação é escrita **junto** com a mudança, não depois. Se uma mudança de código
invalida uma nota, a correção da nota faz parte da mesma mudança — não vira tarefa futura,
porque tarefa futura de documentação não acontece.
