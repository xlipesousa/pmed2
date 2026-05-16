# PR: Retomada Docker + Governanca Fases A-G

## Titulo sugerido
chore(retomada): consolidar docker canonico, higiene git e governanca fases A-G

## Base e Head
- base: main
- head: refactor/remove-upgrade-web-phase1

## Resumo
Esta PR consolida a retomada da aplicacao com foco em:
- Infra Docker canonica para desenvolvimento e deploy.
- Saneamento de tracking indevido (vendor/node_modules/.env).
- Tratamento de arquivos sensiveis no repositório.
- Workflows CI/CD para homologacao/producao com rollback.
- Relatorio faseado de execucao e governanca operacional.

## Escopo principal
- Novos arquivos de infraestrutura Docker e compose canônico.
- Ajustes de CI/CD em [cd-homolog](.github/workflows/cd-homolog.yml) e [cd-prod](.github/workflows/cd-prod.yml).
- Build e publicacao de imagem em [docker-build](.github/workflows/docker-build.yml).
- Ajuste de leitura de segredo de DB em [config/database.php](config/database.php).
- Atualizacao e consolidacao do plano em [plano-execucao](docs/plano-execucao-faseado-retomada-docker.md).

## Validacoes realizadas
- Branch sincronizada com remoto (`git push -u origin refactor/remove-upgrade-web-phase1`).
- Delta para `main`: 15 commits a frente, 0 atras.
- Revisao documental e tecnica da retomada concluida.

## Checklist de revisao
- [x] Alteracoes de infraestrutura revisadas.
- [x] Fluxo CI/CD revisado (homolog e producao).
- [x] Sensiveis tratados conforme plano.
- [ ] Aprovar ajustes recomendados de seguranca em workflows self-hosted.
- [ ] Aprovar merge para main.

## Riscos residuais
- Fase F (deprecacao/remocao de scripts legados) segue em janela planejada de governanca.
- Melhorias de hardening de runner self-hosted recomendadas antes de promover alteracoes para producao.
