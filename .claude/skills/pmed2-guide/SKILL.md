---
name: pmed2-guide
description: Orienta o Claude sobre o domínio de negócio, arquitetura e histórico do projeto PMED 2.0 (sistema de gestão de faturas hospitalares de um plano de saúde do Exército, FUSEx). Use esta skill sempre que trabalhar neste repositório — ao explicar o sistema, ler ou modificar código de Pacotes/Guias/Mapas/Glosas, mexer em controllers ou models de faturamento, investigar rotas ou perfis de usuário, responder dúvidas sobre o fluxo Protocolo→Lisura→SIRE→Glosa→Arquivo, lidar com versionamento/tags/CI-CD/deploy (GitHub Actions, homolog, produção), ou encontrar arquivos legados (install.sh, update.sh, migrate.sh, referências ao GitHub Copilot, docker-compose.yml antigo). Também dispare para perguntas genéricas como "como funciona esse projeto", "o que é uma glosa", "como faço deploy", mesmo sem menção explícita a "PMED".
---

# Guia do Projeto PMED 2.0

## O negócio

O cliente é um **plano de saúde do Exército (FUSEx)**. Ele paga procedimentos médicos que
**conveniados** (beneficiários) realizam em **prestadores de serviço** (hospitais/clínicas
credenciados). O PMED 2.0 é o sistema que controla esse pagamento: gerencia o ciclo de vida
de **pacotes** — lotes de **faturas/guias** de procedimentos que os prestadores enviam ao
plano de saúde para cobrança.

Pense nele como um sistema de contas a pagar B2B com um pipeline de auditoria no meio: nada é
pago sem passar por conferência e possível contestação (glosa).

## Fluxo operacional (o coração do sistema)

```
Protocolo → Lisura → SIRE → Glosa → Arquivo → Arquivado
```

⚠️ **Isto é simplificação didática.** O fluxo real tem ramificação (o SIRE decide entre 4
destinos com base em 7 casos) e três exceções em que uma equipe age sobre pacote que **não
está com ela** — regra de negócio deliberada. Antes de mexer em transição ou autorização,
leia `docs/10-dominio/Ações por equipe.md` e `docs/10-dominio/Estados do pacote.md`. O
desenho completo está em `docs/diagram/pmed2.drawio.svg` (ver
`docs/10-dominio/Fluxograma original.md`).

🔴 **Há uma inconsistência de dados ativa em produção.** Recursos de glosa foram cadastrados
como pacotes novos, travando os pacotes originais e poluindo a base. O caminho já foi
bloqueado, mas os registros ruins continuam lá. Se a tarefa envolver contagem de pacotes,
relatórios, totais financeiros ou pacotes parados em `Aguardando Recurso de Glosa`, leia
`docs/10-dominio/Bug do protocolo.md` antes de concluir qualquer coisa a partir dos números
— eles estão distorcidos. Investigação em `specs/001-caca-bug-protocolo/`.

Cada estágio também é um **perfil de usuário** — ou seja, os nomes de papel no sistema
(`Protocolo`, `Lisura`, `SIRE`, `Glosa`, `Arquivo`, mais `Admin`, `Auditor`, `Pagamento`) mapeiam
diretamente para quem trabalha em cada etapa do fluxo, não são só rótulos de permissão
genéricos. Se você vir uma feature que restringe algo a um desses perfis, ela está modelando
uma etapa do fluxo, não uma hierarquia arbitrária.

| Estágio/Perfil | O que acontece |
|---|---|
| Protocolo | Entrada do pacote de faturas no sistema |
| Lisura | Análise de conformidade e abertura de glosas |
| SIRE | Autorização de pagamento |
| Glosa | Gestão de recursos/contestações sobre valores glosados |
| Arquivo | Arquivamento do pacote já processado |
| Pagamento | Mapas de pagamento (agregam pacotes aprovados) |
| Auditor | Só visualização, sem poder de alterar nada |
| Admin | Acesso total |

## Glossário de domínio

Ao ler código, esses termos aparecem constantemente — vale reconhecê-los em vez de tratá-los
como nomes genéricos de variável:

- **Cadeia do domínio: Pacote (1) → Fatura (1) → Guia (1..N).** Errar essa cardinalidade é o
  engano mais comum — a própria especificação original a descreve mal.
- **Pacote** — a unidade de controle que percorre o fluxo (veja `app/Models/Pacote.php`,
  `PacotesController`). Contém **exatamente uma** fatura.
- **Fatura** — a cobrança apresentada pela OCS/PSA. É 1:1 com o pacote e por isso está
  modelada como atributos dele (`numero_fatura`, `valor_fatura`), não como tabela.
- **Guia** — o documento que formaliza **o atendimento médico realizado**; seu valor soma na
  fatura. Uma fatura tem 1..N guias. ⚠️ **A guia não existe no sistema** — sem tabela, model
  ou tela; a palavra não aparece no código. Feature solicitada pelo cliente, ver
  `docs/40-decisoes/P-24.md` e `specs/002-rastreamento-guias/`.
- **Glosa** / **MotivoGlosa** — contestação/recusa (parcial ou total) de um valor cobrado, e o
  motivo formal dessa recusa.
- **Lisura** — etapa de auditoria/conformidade, não um sinônimo de "análise" genérica.
- **SIRE** — sistema/etapa de autorização de pagamento (nome próprio do domínio, não uma sigla
  a expandir por conta própria — trate como dado, não como algo a "corrigir").
- **Mapa** (de pagamento) — agrupamento de pacotes aprovados para uma remessa de pagamento.
- **OCS/PSA** — tipos/categorias de unidades prestadoras de serviço, configuráveis em
  `ConfiguracoesController`.
- **Prestador** — hospital/clínica credenciada que envia as faturas.
- **Conveniado** — beneficiário do plano de saúde (o paciente).

Se uma tarefa mexe em qualquer um desses conceitos, vale abrir o cofre de documentação em
`docs/Home.md` antes de assumir o comportamento esperado — é a fonte da verdade do projeto,
mais detalhada e mais atual que o README. (Uma versão anterior desta skill apontava para um
`relatorio.md` na raiz — esse arquivo nunca existiu no repositório; se você encontrar essa
referência em outro lugar, é vestígio e deve ser corrigida para `docs/Home.md`.)

## Stack técnica

Laravel 12 / PHP 8.2+ / MySQL-MariaDB / Blade + AdminLTE 3 / Bootstrap 5 + Tailwind 4 / Vite.
Controllers principais ficam em `app/Http/Controllers/`, models em `app/Models/`, lógica
auxiliar em `app/Helpers/` (ex.: `DashboardHelper`, `AtividadesHelper`).

## Vestígios de migração — leia antes de confiar em arquivos "óbvios"

Este projeto está passando por duas transições que deixaram artefatos para trás. **Antes de
seguir uma instrução escrita num arquivo antigo do repo (README, scripts), confirme se ela
ainda reflete a forma atual de operar** — não assuma que documentação = comportamento atual.

1. **GitHub Copilot → Claude**: o projeto era desenvolvido com Copilot. Se encontrar arquivos
   de instruções, comentários ou convenções que parecem geradas para orientar um assistente de
   código diferente, trate como histórico, não como fonte de verdade — mas não apague sem
   perguntar, pode haver contexto de negócio útil ali.

2. **Instalação/deploy via shell script → Docker + CI/CD**: o README ainda documenta
   `install.sh` / `update.sh` como forma de instalar e atualizar o sistema, e a raiz tem vários
   scripts `.sh` (`install.sh`, `update.sh`, `migrate.sh`, `deploy-docs.sh`, além dos que estão
   em `scripts/`: `backup.sh`, `deploy.sh`, `rollback.sh`, etc.). Isso é o modelo **antigo**.
   O modelo **atual** é containerizado: existem múltiplos `docker-compose*.yml` e
   `deploy/compose.*.yml` (homolog, produção, produção-filial), e o deploy real acontece via
   GitHub Actions (veja abaixo) rodando em runners self-hosted, não via SSH + script manual.
   Ao propor mudanças de infraestrutura, prefira o caminho Docker/Actions — os scripts `.sh`
   podem estar desatualizados em relação a ele. `scripts/backup.sh` é exceção: ele roda em
   runtime dentro do container `scheduler` e entra na imagem via `COPY . .` no Dockerfile, então
   continua ativo e versionado (veja o `.gitignore` — os demais scripts de `scripts/` são
   ignorados, esse não é).

   Há também dois compose "locais" concorrentes na raiz — `docker-compose.yml` (mais antigo) e
   `docker-compose.retomada.yml`/`docker-compose.retomada.override.yml` (a stack canônica atual,
   ligada ao esforço de "retomada" do projeto). Se não tiver certeza de qual usar, pergunte —
   não assuma que `docker-compose.yml` é o principal só por ter o nome mais curto.

## Versionamento e CI/CD

Releases seguem tags `vX.Y.Z` (a versão em `composer.json` deve bater com a tag mais recente —
confira com `git describe --tags` ou o campo `version` do `composer.json`, **nunca confie num
número escrito em prosa aqui**, ele vai ficar desatualizado). O pipeline é todo GitHub Actions,
em runners self-hosted (`pmed2-interno`):

- **`ci.yml`** — roda em todo push/PR para `main` (testes, lint).
- **`docker-build.yml`** — builda e publica a imagem no GHCR em push para `main` ou em tag
  `v*.*.*`.
- **`cd-homolog.yml`** — dispara **automaticamente** ao dar push numa tag `v*.*.*`, faz deploy
  direto no ambiente de homologação.
- **`cd-prod.yml`** — **não** dispara sozinho. É `workflow_dispatch` manual, com o ambiente
  GitHub `production` (que tem proteção/aprovação configurada) — ou seja, alguém precisa
  disparar manualmente o workflow (escolhendo `deploy` ou `rollback`) depois de validar em
  homolog. Se o usuário pedir para "fazer um release", o fluxo correto é: bump de versão →
  criar tag `vX.Y.Z` → push da tag (dispara homolog sozinho) → validação manual → disparo manual
  do `cd-prod.yml`.

Ao mexer em qualquer um desses workflows, lembre que uma mudança aqui afeta deploys reais em
produção — trate como alto risco, confirme com o usuário antes de alterar gatilhos ou o
ambiente `production`.

## Skills relacionadas neste projeto

- **`docs/Home.md`** — o cofre Obsidian versionado é a fonte da verdade da documentação
  (domínio, arquitetura, ADRs, runbooks). Esta skill é um resumo de bolso; se algo aqui
  divergir do cofre, o cofre está certo.
- `run-pmed2` (`.claude/skills/run-pmed2/`) — sobe o stack Docker, roda migrations, tira
  screenshot da UI, faz login. Use para *rodar* o app; esta skill (`pmed2-guide`) é para
  *entender* o app.
- `pmed2-deploy` — fluxo de release ponta a ponta, incluindo o gate de validade dos tokens
  do GHCR antes de qualquer disparo de `cd-homolog.yml`/`cd-prod.yml`.
- `pmed2-spec` — escrever e executar uma spec de mudança segundo a convenção do projeto.
- Se `graphify-out/graph.json` existir na raiz, ele contém um grafo de conhecimento já mapeado
  do código — útil para perguntas tipo "o que chama X" ou "como Y se conecta a Z" (veja a skill
  `graphify`). **Nunca rode `graphify --obsidian`** — sobrescreveria o cofre `docs/` escrito à
  mão com um cofre gerado automaticamente.
