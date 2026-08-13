---
title: ADRs
tags:
  - decisoes
  - moc
---

# ADRs — Architecture Decision Records

Registradas durante a migração para Docker (homologação em 2026-08-10, produção em
2026-08-11). Cada uma documenta o *porquê*, não só o *o quê* — é o que permite julgar se a
decisão ainda vale quando o contexto mudar.

Fonte original: `planos/guia-decisoes-producao.md` (versionado, mantido como registro
primário da migração de infraestrutura).

| ADR | Decisão | Resultado |
|---|---|---|
| [[ADR-01]] | MySQL permanece no host, não é containerizado | Confirmada nos dois ambientes |
| [[ADR-02]] | nginx do host vira proxy reverso; container publica em `127.0.0.1:8080` | Confirmada |
| [[ADR-03]] | Adotar Redis para cache e fila | Confirmada — foi o que fez o backup automático voltar a rodar |
| [[ADR-04]] | Cutover paralelo, nunca big-bang | Confirmada, com ressalva sobre banco compartilhado |
| [[ADR-05]] | Segredo lido pelo container sem rodar como root | Dívida paga via ACL, não `chown` |
| [[ADR-06]] | `APP_KEY` propagado do `shared/.env` do servidor | Confirmada — nunca gerar chave nova |
| [[ADR-07]] | Segredos por arquivo, não por variável de ambiente | Confirmada, com incidente real que a motivou |
| [[ADR-08]] | MySQL escuta na subnet Docker fixa do compose | A mais complexa — 3 correções até funcionar |
| [[ADR-09]] | `memory_limit` explícito na imagem (512M) | Confirmada — é mitigação, ver [[Pendências\|P-15]] |
| [[ADR-10]] | Egress corporativo precisa liberação por ambiente | Confirmada — homolog e prod têm políticas diferentes |
| [[ADR-11]] | Token do GHCR precisa ser PAT classic | Confirmada — causou um incidente real de deploy |

## Produto

| ADR | Decisão | Resultado |
|---|---|---|
| [[ADR-12]] | O prazo de 30 dias avisa, nunca dispara ação automática | Confirmada pelo cliente — mas o aviso não existe ([[P-23]]) |
