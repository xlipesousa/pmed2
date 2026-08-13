---
name: pmed2-deploy
description: Fluxo de release e deploy do PMED2 (tag → homolog automático → validação → produção manual com aprovação). Use sempre que for fazer um release, disparar cd-homolog.yml/cd-prod.yml, investigar um deploy que falhou, ou precisar de rollback. Dispara também para perguntas como "como faço deploy", "como faço um release", "preciso reverter o deploy".
---

# pmed2-deploy

Guia operacional do fluxo de release. O runbook completo, mantido, vive em
`docs/30-operacao/Runbook de deploy.md` e `docs/30-operacao/Runbook de rollback.md` — esta
skill é o resumo acionável; se divergir do cofre, o cofre está certo.

## Antes de qualquer disparo — gate obrigatório

> [!IMPORTANTE] Checar a validade dos tokens do GHCR **antes** de disparar
> `cd-homolog.yml` e `cd-prod.yml` puxam a imagem de um pacote GHCR privado com um PAT
> **classic** (nunca fine-grained — ver `docs/40-decisoes/ADR-11.md`), válido por 90 dias.
>
> ```bash
> gh secret list --repo xlipesousa/pmed2 | grep GHCR_TOKEN
> ```
>
> Datas conhecidas (atualizar aqui e em `docs/40-decisoes/P-10.md` a cada rotação):
> - `PMED2_HOM_GHCR_TOKEN`: criado 2026-08-10 → expira ~2026-11-08
> - `PMED2_PROD_GHCR_TOKEN`: criado 2026-08-11 → expira ~2026-11-09
>
> Um token vencido não falha no `docker login` — falha no `pull`, **depois** de outros steps
> já terem mutado estado no servidor (secret gravado, sessão SSH aberta). Se estiver a menos
> de ~2 semanas do vencimento ou já vencido, avise o usuário e ofereça esperar a rotação
> (`gh secret set PMED2_*_GHCR_TOKEN`, sempre PAT classic) antes de disparar.

## Fluxo

```
bump composer.json → tag vX.Y.Z → push
  → docker-build.yml (publica no GHCR) + cd-homolog.yml (dispara sozinho, deploy em homolog)
  → validar em homolog (login real, /pacotes com dados, migrate:status)
  → gh workflow run cd-prod.yml -f mode=deploy -f tag=vX.Y.Z
  → aprovação obrigatória (required reviewer no environment "production")
  → validar em produção
```

`composer.json.version` é a fonte da verdade da versão — o CI valida contra semver, a UI lê
de lá. Bump e tag sempre juntos, nunca deixar divergir.

## Comandos

```bash
# release
composer validate --strict
git tag vX.Y.Z && git push origin main --tags

# acompanhar homolog (automático)
gh run list --workflow=cd-homolog.yml --limit 3
gh run watch <run-id>

# disparar produção
gh workflow run cd-prod.yml -f mode=deploy -f tag=vX.Y.Z
gh run list --workflow=cd-prod.yml --limit 3
# aprovar via UI do GitHub (Actions → run → Review deployments), depois:
gh run watch <run-id>
```

## Verificação — não caia nas armadilhas conhecidas

| Armadilha | Por quê engana | Use no lugar |
|---|---|---|
| `/health` → 200 | Rota estática, não toca no banco | `migrate:status` dentro do container |
| `curl -I .../login` | Sem `-f`, não falha em HTTP 500 | `curl -fsS` + `grep -c csrf-token` |
| `docker login` com sucesso | Não prova que o `pull` vai funcionar | O `pull` de verdade |

```bash
curl -fsS http://pmed2.4rm.eb.mil.br/health
docker compose -f deploy/compose.prod.yml exec -T app php artisan migrate:status | tail -5
```

## Rollback

Dois níveis independentes — ver `docs/30-operacao/Runbook de rollback.md`:

1. **Reverter só o nginx** (`sudo ./cutover-nginx-prod.sh rollback`) — mais rápido, restaura
   o bare-metal em segundos. Só serve para problema de infraestrutura da stack nova, não de
   bug na aplicação.
2. **`mode: rollback` do `cd-prod.yml`** — reverte a versão da aplicação.
   ```bash
   gh workflow run cd-prod.yml -f mode=rollback -f tag=vX.Y.Z -f rollback_tag=vX.Y.(Z-1)
   ```
   ⚠️ Nunca foi exercitado de verdade em produção (`docs/40-decisoes/P-16.md`). Trate com
   cautela extra e valide o resultado com cuidado redobrado.

## Restrições do ambiente

- **Não há SSH direto** aos servidores — só Teleport, operado por um humano. Comandos
  server-side não podem ser executados por este agente.
- **Nunca gere um `APP_KEY` novo** — invalida todas as sessões e torna ilegível todo dado
  encriptado no banco.
- **Nunca interpole `${{ secrets.X }}` no corpo de um `run:`** ao editar os workflows —
  sempre pelo bloco `env:` do step.
