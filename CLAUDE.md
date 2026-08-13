# PMED 2.0

Sistema de controle de pagamento de faturas hospitalares do **FUSEx** (plano de saúde do
Exército Brasileiro). Contas a pagar B2B com um pipeline de auditoria no meio.

**A fonte da verdade é o cofre em [`docs/Home.md`](docs/Home.md).** Este arquivo é só um
ponteiro rápido — não duplique conteúdo dele aqui; se algo mudar, corrija lá.

## Domínio, em uma frase

Prestadores enviam **pacotes** de faturas; o sistema audita (**Lisura**), autoriza
(**SIRE**), e contesta valores quando cabe (**Glosa**), antes de pagar. Glossário completo:
[`docs/00-projeto/Glossário.md`](docs/00-projeto/Glossário.md).

> [!WARNING]
> **Antes de mexer em qualquer transição do pacote**, leia
> [`docs/10-dominio/Ações por equipe.md`](docs/10-dominio/Ações%20por%20equipe.md). O fluxo
> `Protocolo → Lisura → SIRE → Glosa → Arquivo` é simplificação: o SIRE ramifica em 7 casos,
> e há **três pontos onde uma equipe age sobre pacote que não está com ela** — regra de
> negócio, não bug. Autorização escrita assumindo "uma equipe por localização" quebra o
> fluxo de recurso de glosa.

## Como rodar

**Docker, não `install.sh`.** O README ainda menciona o modelo antigo em alguns pontos —
não confie nele sem checar a data. Para desenvolvimento local, use a skill `run-pmed2`.
Detalhes de stack e ambientes: [`docs/20-arquitetura/Stack e ambientes.md`](docs/20-arquitetura/Stack%20e%20ambientes.md).

## Deploy

Tag `vX.Y.Z` → build automático + deploy automático em homolog → validação → disparo
manual de `cd-prod.yml` com aprovação obrigatória. Runbook completo, incluindo o gate de
validade dos tokens do GHCR (90 dias): [`docs/30-operacao/Runbook de deploy.md`](docs/30-operacao/Runbook%20de%20deploy.md).

Não há SSH direto aos servidores — apenas Teleport, operado por um humano.

## Convenções

- **Conventional Commits.**
- `composer.json.version` é a fonte da verdade da versão — o CI valida contra semver, a UI
  lê de lá. Nunca escreva número de versão em prosa em documentação.
- **Nunca interpole `${{ secrets.X }}` no corpo de um `run:` de workflow.** Sempre pelo
  bloco `env:` do step — já vazou segredo em log uma vez por violar isso.
- **Nunca gere um `APP_KEY` novo.** Invalida todas as sessões e torna ilegível todo dado
  encriptado no banco.
- **`docs/` é escrito à mão e é a fonte da verdade.** `graphify-out/` é derivado do código
  por extração e é descartável. **Nunca rode `graphify --obsidian`** — ele geraria um cofre
  próprio e sobrescreveria a documentação escrita à mão.

## ⚠️ Inconsistência de dados ativa em produção

Recursos de glosa foram cadastrados como **pacotes novos** durante um período. O caminho foi
bloqueado, mas os registros ruins continuam na base: pacotes originais travados em
`Aguardando Recurso de Glosa`, e contagens/valores inflados nos relatórios.

**Não tire conclusões de contagem de pacotes ou de totais financeiros de produção sem ler
antes** [`docs/10-dominio/Bug do protocolo.md`](docs/10-dominio/Bug%20do%20protocolo.md).
Investigação planejada em [`specs/001-caca-bug-protocolo/`](specs/001-caca-bug-protocolo/spec.md).

## Estado do código

Este foi o primeiro sistema do autor construído com geração de código via chat, sem revisão
estrutural. Isso deixou marcas catalogadas — **leia antes de assumir comportamento**:
[`docs/20-arquitetura/Dívida técnica.md`](docs/20-arquitetura/Dívida%20técnica.md). Dois
pontos que mordem quem não sabe:

- **Zero camada de negócio** — toda a lógica está em controllers.
- **A suíte de testes reporta verde sem executar nada** (`markTestSkipped` em cascata, sem
  `RefreshDatabase`). Não confie em "os testes passam" como prova de nada até isso ser
  corrigido — ver [`docs/20-arquitetura/Dívida técnica.md`](docs/20-arquitetura/Dívida%20técnica.md).

Plano de normalização em andamento: `planos/plano-normalizacao-pmed2.md`.

## Specs

Mudanças estruturais (refatoração, nova feature de peso) seguem a convenção em `specs/` —
uma spec por mudança, com fases e critério de aceite, e um `estado.md` que só marca tarefa
como concluída com evidência colada. Ver a skill `pmed2-spec`.

## Skills deste projeto

| Skill | Para quê |
|---|---|
| `pmed2-guide` | Entender o domínio e a arquitetura |
| `run-pmed2` | Rodar a stack localmente |
| `pmed2-deploy` | Fluxo de release, com o gate de validade dos tokens do GHCR |
| `pmed2-spec` | Escrever e executar uma spec segundo a convenção do projeto |
| `obsidian-markdown`, `obsidian-bases`, `json-canvas`, `obsidian-cli`, `defuddle` | Trabalhar no cofre `docs/` |
