# Promoção para Produção Oficial e Governança GitHub

## 1) Promoção para produção oficial

1. Validar pré-requisitos no host de produção:
   - estrutura `/var/www/pmed2/{releases,current,shared,backups}`;
   - `shared/.env` válido;
   - scripts em `/var/www/pmed2/shared/scripts` executáveis.
2. Configurar secrets do ambiente oficial no GitHub:
   - `PMED2_SSH_HOST` (IP/hostname da produção);
   - `PMED2_SSH_USER` (usuário de deploy);
   - `PMED2_SSH_KEY` (chave privada correspondente).
3. Criar tag de promoção:

```bash
git tag -a v0.2.0 -m "Promoção inicial produção oficial"
git push origin v0.2.0
```

4. Validar pós-deploy:

```bash
readlink -f /var/www/pmed2/current
curl -fsS http://127.0.0.1/health
ls -1t /var/www/pmed2/backups | head
```

## 2) Governança no GitHub (branch protection)

Aplicar em `Settings > Branches > Add branch protection rule` para `main`:

- Require a pull request before merging.
- Require approvals: mínimo 1.
- Dismiss stale approvals when new commits are pushed.
- Require status checks to pass before merging:
  - `quality`
  - `package`
- Require branches to be up to date before merging.
- Restrict who can push to matching branches (bloquear push direto).

## 3) Governança de releases

- Produção oficial só via tag `vX.Y.Z`.
- Manter CHANGELOG por release.
- Em incidente: rollback imediato via `shared/scripts/rollback.sh` e abertura de post-mortem.
