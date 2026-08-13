# Registro de Descontinuação — Upgrade Web

## Status
- Situação: descontinuado
- Data de descontinuação: 2026-03-04
- Motivação: consolidação do modelo de entrega via CI/CD e redução de risco operacional no runtime da aplicação.

## Contexto
A funcionalidade de upgrade via painel administrativo (rota `/configuracoes/upgrade`) permitia acionar operações críticas de atualização da aplicação a partir da interface web.

Com a evolução da governança de entrega, essa estratégia foi substituída por pipeline CI/CD como canal único de atualização.

## Decisão arquitetural vigente
- Atualizações da aplicação ocorrem exclusivamente via CI/CD.
- A interface web de upgrade foi removida.
- O comando `pmed2:upgrade` e componentes internos associados foram removidos do código.

## Benefícios esperados
- Redução de risco ao eliminar execução de `git/composer/npm/migrate` via request web.
- Menor chance de divergência entre ambiente e artefato publicado.
- Maior rastreabilidade/auditabilidade do processo de atualização no pipeline.

## Referências
- Governança de deploy: [producao-governanca-checklist.md](producao-governanca-checklist.md)
- Plano de remoção controlada: [plano-refatoracao.md](../plano-refatoracao.md)

## Observação
O arquivo `plano-upgrade.md` permanece como artefato local (não versionado) para histórico de trabalho. Este documento em `docs/` é a referência versionada oficial da descontinuação.
