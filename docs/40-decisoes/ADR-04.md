---
title: "ADR-04 — Cutover paralelo, nunca big-bang"
tags:
  - decisao
  - adr
status: confirmada com ressalva
data: 2026-08-10
ambiente: homolog, producao
---

# ADR-04 — Cutover paralelo, nunca big-bang

## Decisão

Subir a stack Docker em porta alternativa com o bare-metal ainda servindo; validar; só então
virar o tráfego (troca do server block do nginx).

## Razão

Se algo falhar na validação, nada foi perdido — o ambiente antigo nunca parou. O custo é uma
etapa a mais; o benefício é que a janela de indisponibilidade real é o tempo de um
`systemctl reload nginx`.

## Resultado e ressalva importante

Validado no essencial nos dois ambientes — o bare-metal nunca foi parado, e a troca do nginx
não teve risco por rodar em paralelo.

> [!warning] O isolamento não é total
> Os dois ambientes (bare-metal e stack nova) **compartilham o mesmo MySQL do host**. Em
> homologação, um incidente durante o provisionamento (`bind-address` mal configurado, ver
> [[ADR-08]]) derrubou o MySQL e afetou o bare-metal — `/login` deu 500 por ~20 minutos, mesmo
> com a stack nova ainda nem tendo subido.
>
> "Paralelo" protege a aplicação nova de afetar a antiga, mas **não protege a antiga de
> mudanças feitas no host** (banco, rede) durante o provisionamento. Em produção, o mesmo
> passo rodou sem incidente — porque a lição já estava incorporada no script — mas o risco
> estrutural (banco compartilhado) permanece para qualquer mudança futura de host.

Ver [[Stack e ambientes]] e [[Pendências|P-13]].
