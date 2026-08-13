---
title: "ADR-10 — Egress corporativo por ambiente"
tags:
  - decisao
  - adr
status: confirmada
data: 2026-08-11
ambiente: producao
---

# ADR-10 — Egress corporativo para GHCR/Docker Hub precisa de liberação explícita, por ambiente

## Contexto

O diagnóstico read-only de produção mostrou `curl https://ghcr.io/v2/` travando em timeout,
apesar do DNS resolver e do `ufw`/iptables do **host** estarem totalmente abertos. Homolog
nunca teve esse problema. O bloqueio era num firewall **corporativo**, fora do host, não
documentado em nenhum diagnóstico anterior — homolog e produção **não compartilham
necessariamente** a mesma política de egress, mesmo sendo VMs do mesmo template.

## Decisão

Tratar a confirmação de egress como gate explícito antes de qualquer provisionamento,
testado com o método mais forte disponível — não só `curl`, mas `docker pull hello-world`
depois do Docker instalado, porque uma regra pode liberar o `curl` do host e ainda assim
falhar para o caminho de rede do daemon Docker.

## Resultado

Liberação solicitada e confirmada com `401` (não timeout) em `ghcr.io` **e**
`registry-1.docker.io`. Revalidado com sucesso já com o Docker instalado.

> [!warning] Achado colateral
> O mesmo firewall corporativo só libera as portas **80/443** para o operador via Teleport —
> a porta 8080, onde a stack sobe em paralelo antes do cutover, nunca foi acessível para
> teste manual. Forçou adiar a validação de `/pacotes` com dados reais para **logo após** o
> cutover, em vez de antes. Funcionou, mas é lacuna real no isolamento do cutover paralelo
> ([[ADR-04]]) a considerar numa próxima migração com restrição de rede parecida.

## Consequência para o futuro

Não presumir que a liberação de rede de um ambiente vale para outro. Testar cedo, com o
método mais forte disponível.

Ver [[Stack e ambientes]].
