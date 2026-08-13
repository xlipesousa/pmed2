---
title: Histórico
tags:
  - historico
  - moc
---

# Histórico

Documentos de fases já encerradas do projeto. São **registro**, não instrução.

> [!warning] Não siga um passo a passo daqui sem confirmar que ainda vale
> Todos estes documentos descrevem estados anteriores do sistema. Alguns contêm afirmações
> que eram verdadeiras quando escritas e não são mais. Onde divergirem do resto do cofre, o
> resto do cofre está certo.

## Especificação

- [[Sobre a especificação original]] — enquadramento e o que foi extraído dele
- [[Especificação original (diário de bordo)]] — o documento em si

Este é o único documento histórico com valor ativo: ele responde **"por quê"** em pontos onde
o código só mostra o "o quê". Ver a nota de enquadramento antes de usá-lo.

## Migração para Docker (2026-05 a 2026-08)

- [[plano-execucao-faseado-retomada-docker]] — plano faseado de 2026-05. **Declara fases como
  concluídas com base em evidências colhidas no ambiente de desenvolvimento, não em
  servidor.** Nunca menciona que o Docker não estava instalado em nenhum dos servidores — é
  o maior buraco do registro. Ver as ressalvas em `planos/guia-decisoes-producao.md` §4
- [[producao-governanca-checklist]] — checklist de governança, em boa parte ainda não
  executado (ver F2.2 do plano de normalização)
- [[pr-retomada-remove-upgrade-web-phase1]] — corpo do único PR da história do repositório
- [[upgrade-web-descontinuado]] — registro da descontinuação da funcionalidade "Upgrade Web"

O resultado real dessa migração, com as decisões e o que de fato aconteceu, está em [[ADRs]]
e [[Pendências]] — não nestes arquivos.
