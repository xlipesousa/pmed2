---
title: Runbook de rollback
tags:
  - operacao
---

# Runbook de rollback

> [!danger] `mode=rollback` do `cd-prod.yml` nunca foi testado de verdade
> Ver [[P-16]]. O primeiro deploy de produção não teve `previous_tag` para testar contra.
> **Testar explicitamente numa janela controlada antes de precisar dele de emergência** —
> não descobrir se funciona só na hora do incidente.

Há dois níveis de rollback, independentes um do outro.

## Nível 1 — reverter só o nginx (mais rápido, sempre disponível)

Usar quando o problema é da stack Docker nova e o bare-metal antigo ainda está intacto no
host (situação típica logo após um [[ADR-04|cutover paralelo]]).

```bash
sudo ./cutover-nginx-prod.sh rollback   # ou cutover-nginx-homolog.sh
```

Restaura o `server_block` bare-metal a partir do backup (`pmed2.baremetal.bak`) e recarrega
o nginx. **O bare-metal nunca foi parado durante o cutover** — isso volta a servir em
segundos, sem tocar em container nem em banco.

Só funciona enquanto o bare-metal antigo continuar instalado e configurado no host. Depois
que [[Dívida técnica|o php8.3-fpm bare-metal for desabilitado]] (planejado, mas deliberadamente
adiado até haver pelo menos um ciclo estável), esse caminho deixa de existir.

## Nível 2 — `mode: rollback` do `cd-prod.yml`

Usar quando o problema está na versão da aplicação (bug introduzido na tag atual), não na
infraestrutura — e o Nível 1 não resolve porque o problema persistiria na tag anterior
também servida pela mesma stack.

```bash
gh workflow run cd-prod.yml -f mode=rollback -f tag=vX.Y.Z -f rollback_tag=vX.Y.(Z-1)
```

Requer aprovação manual pelo `required reviewer` do environment `production`, igual a um
deploy normal.

> [!warning] Antes de usar em produção de verdade
> Testar contra uma tag conhecida numa janela planejada. Confirmar que:
> - o `pull` da imagem `rollback_tag` funciona;
> - `migrate:status` depois do rollback não mostra migrations "pendentes" que a versão
>   anterior não reconhece (rollback de schema **não** é automático — se a tag atual rodou
>   migrations novas, o rollback de código não desfaz o schema);
> - a aplicação sobe saudável na tag alvo.

## Antes de qualquer rollback

Checar `git log`/`gh release list` para confirmar qual é a última tag **conhecida como
boa** — não assumir que é simplesmente "a anterior" sem checar o histórico de deploys
recentes.

```bash
gh run list --workflow=cd-prod.yml --limit 10
```

## Depois do rollback

1. Validar (`/health`, `migrate:status`, login real) — mesmos passos do
   [[Runbook de deploy]].
2. Registrar o incidente: o que quebrou, qual rollback foi usado, tempo até resolução.
3. Não disparar a tag problemática de novo sem entender a causa raiz.
