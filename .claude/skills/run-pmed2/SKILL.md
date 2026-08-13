---
name: run-pmed2
description: Build, run, and drive PMED2 (Laravel hospital billing app running in Docker). Use when asked to start pmed2, bring up its stack, run migrations, run its tests, take a screenshot of its UI, log in, or interact with the running app.
---

PMED2 is a Laravel 12 + AdminLTE web app served through Docker Compose
(nginx → PHP-FPM `app`, plus `queue`, `scheduler`, `db` (MariaDB), `redis`).
There is no `chromium-cli` in this environment, so it's driven via the
Playwright REPL at `.claude/skills/run-pmed2/driver.mjs`, which drives the
system Chromium (`/usr/bin/chromium`) headless.

All paths below are relative to the repo root (`pmed2/`).

## Prerequisites

```bash
# System Chromium and tmux were already present in this environment.
# If missing:
sudo apt-get update && sudo apt-get install -y chromium tmux
docker --version && docker compose version   # Docker Engine + Compose v2
```

## Setup

```bash
composer install --no-interaction --no-progress
npm install
npm run build                       # generates public/build/* (needed by nginx bind-mount)
cp .env.example .env
php artisan key:generate            # writes APP_KEY into .env

mkdir -p .secrets
openssl rand -base64 24 | tr -d '/+=' | head -c 24 > .secrets/db_password
echo "" >> .secrets/db_password
openssl rand -base64 24 | tr -d '/+=' | head -c 24 > .secrets/db_root_password
echo "" >> .secrets/db_root_password
chmod 600 .secrets/db_password .secrets/db_root_password
```

`docker-compose.yml`'s `app`/`queue`/`scheduler` services read `APP_KEY`
from the shell environment via `${APP_KEY:?...}` — Docker Compose auto-loads
`.env` from the repo root for that interpolation, so the step above is
enough; you don't need to `export` it manually.

Driver's own dependency (installed once, local to the skill dir — does not
touch the app's `package.json`):

```bash
cd .claude/skills/run-pmed2 && npm install playwright-core --no-save && cd -
```

## Build

```bash
docker compose up -d --build   # first build compiles PHP extensions (gd, redis via pecl) — takes several minutes
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --class=AdminUserSeeder --force
```

## Run (agent path)

```bash
docker compose up -d           # if not already up
tmux new-session -d -s pmed2driver -x 200 -y 50
tmux send-keys -t pmed2driver 'node .claude/skills/run-pmed2/driver.mjs' Enter
timeout 15 bash -c 'until tmux capture-pane -t pmed2driver -p | grep -q "driver>"; do sleep 0.3; done'
tmux send-keys -t pmed2driver 'launch' Enter
timeout 20 bash -c 'until tmux capture-pane -t pmed2driver -p | grep -q "launched"; do sleep 0.3; done'
tmux send-keys -t pmed2driver 'login' Enter
timeout 15 bash -c 'until tmux capture-pane -t pmed2driver -p | grep -q "login →"; do sleep 0.3; done'
tmux send-keys -t pmed2driver 'ss dashboard' Enter
timeout 10 bash -c 'until tmux capture-pane -t pmed2driver -p | grep -q "screenshot:"; do sleep 0.3; done'
tmux capture-pane -t pmed2driver -p
```

Screenshots land in `/tmp/pmed2-shots/` (override: `SCREENSHOT_DIR`).
App URL defaults to `http://localhost:8080` (override: `PMED2_URL`).
Stop with `tmux send-keys -t pmed2driver 'quit' Enter` then `tmux kill-session -t pmed2driver`.

### Driver commands

| command | what it does |
|---|---|
| `launch` | start headless Chromium |
| `nav [path\|url]` | navigate; bare `nav` goes to `/` |
| `login [email] [password]` | fills `/login`, submits; defaults to `admin@admin` / `admin` |
| `ss [name]` | full-page screenshot → `/tmp/pmed2-shots/<name>.png` |
| `click <css-sel>` | click element |
| `click-text <text>` | click first element containing text |
| `fill <css-sel> <value>` | fill an input |
| `type <text>` / `press <key>` | keyboard input |
| `wait <css-sel>` | wait up to 10s for element |
| `eval <js-expr>` | evaluate JS in page, print JSON |
| `text [css-sel]` | print `innerText` (body if no selector) |
| `url` | print current page URL |
| `console-errors` | print console errors captured since `launch` |
| `quit` | close browser, exit driver |

## Run (human path)

```bash
docker compose up -d
xdg-open http://localhost:8080   # or just open it in a browser; login admin@admin / admin
docker compose down              # stop
```

## Test

```bash
timeout 90 ./vendor/bin/phpunit --testdox
```

Uses an in-memory SQLite DB (`phpunit.xml`), independent of the Docker
stack. Verified result in this environment: `Tests: 4, Assertions: 2,
Skipped: 2` — 2 example tests pass, 2 are pre-existing skips (not
introduced by this skill).

---

## Gotchas

- **AJAX-rendered pages need an explicit `wait` before `ss`.** `nav` only
  waits for `domcontentloaded`. Pages like `/pacotes` render a DataTables
  grid via a follow-up AJAX call — a screenshot taken right after `nav`
  shows only the loading spinner logo. Always `wait table` (or another
  selector specific to the page) before `ss` on anything past a plain
  static view.
- **`app` container must run as root (`user: "0:0"` in `docker-compose.yml`)
  to read `.secrets/db_password`.** The file is `chmod 600`, owned by the
  host user; the image's default `www-data` (uid 82) can't read it. Don't
  "fix" this by loosening the secret file's permissions — the fix is
  already in `docker-compose.yml` for `app`, `queue`, and `scheduler`.
  **This is local-dev-only and intentionally different from homolog/prod**:
  those environments pay this same permission problem with a POSIX ACL
  (`setfacl -m u:82:r`, no root needed) instead of running as root — see
  `docs/40-decisoes/ADR-05.md`. Don't "fix" this file to match that pattern;
  local dev's throwaway secret doesn't carry the same risk, and the ACL
  approach requires the `acl` package which isn't part of this setup.
- **`queue`/`scheduler` bind-mount the repo root**, so `composer install`
  and `npm run build` (the Setup section above) must have been run on the
  **host**, not just baked into the image — otherwise those two containers
  boot against a `vendor/`-less, asset-less tree.
- **First `docker compose up -d --build` is slow (5-10 min).** It compiles
  `gd` and `redis` (via `pecl`) from source in the Alpine PHP image. Not
  hung — just slow. Rebuilds after that are cached and fast.
- **nginx healthcheck must target `127.0.0.1`, not `localhost`.** Alpine's
  `/etc/hosts` resolves `localhost` to `::1` first and nginx only listens
  on `0.0.0.0:80`, so a `localhost`-based healthcheck always fails even
  though the app is fine. `docker-compose.yml`'s healthcheck already uses
  `127.0.0.1`.

## Troubleshooting

- **`502 Bad Gateway` from nginx:** `app` container is down/crash-looping —
  check `docker compose logs app --tail=30`. Common cause: missing
  `APP_KEY` (re-check `.env` has it and `docker compose config` shows it
  interpolated) or the secret-permission issue above.
- **`500` on every page, log shows `Base table or view not found`:**
  migrations haven't run — `docker compose exec app php artisan migrate --force`.
- **Login screen renders unstyled / 404s on `/build/assets/*`:** `npm run
  build` wasn't run on the host before `web`'s bind-mount picked up the
  tree — re-run `npm run build`.
- **Driver hangs on `launch`:** `CHROMIUM_PATH` (default `/usr/bin/chromium`)
  doesn't exist on this machine — check `which chromium` and set the env
  var to the real path.
- **`500` on `/pacotes`, log shows `Allowed memory size ... exhausted` at
  `PhpEngine.php`:** known anti-pattern, see "Refactor backlog" below —
  the immediate infra mitigation (not a code fix) is bumping
  `php_admin_value[memory_limit]` in the `php8.3-fpm` pool config and
  reloading the service; it buys time but doesn't remove the root cause.

## Refactor backlog — "load full table, render N tabs" anti-pattern

> [!Full detail now lives in the vault]
> This section is kept as historical context for how the mitigation was
> shipped. The current, maintained version of this analysis — including
> the fix plan and its priority — is `docs/20-arquitetura/Dívida técnica.md`
> (item F4.2 of `planos/plano-normalizacao-pmed2.md`). If the two disagree,
> the vault is current and this is stale.

**Found in production 2026-07-09**: `PacotesController::index()` loaded
the *entire* `pacotes` table (`Pacote::with([...])->get()`, no `select()`,
no pagination, no filtering) and `resources/views/pacotes/index.blade.php`
iterated that same collection **6 times** (once per tab: protocolo,
lisura, sire, glosa, arquivo, arquivado), filtering in-template with
`@if`. Fine with a handful of rows; fatal once the table grows — caused
`Allowed memory size of 134217728 bytes exhausted` 500s in production
(v2.1.4) while homolog (fewer rows) was unaffected.

**Mitigation shipped** (`app/Http/Controllers/PacotesController.php`,
uncommitted as of 2026-07-09): trimmed to `select()` only the ~13 columns
the view actually reads, trimmed eager-loaded relations to
`ocsPsa:id,nome` / `tipoPacote:id,nome`, dropped the unused `tipoConta`
eager load entirely. Measured ~80% memory reduction (3,141 KB → 621 KB
for 180 rows) with zero visible behavior change (verified via
`run-pmed2` driver screenshots of all 6 tabs). **This is a mitigation,
not a fix** — the query still loads the whole table; it just costs less
per row. Production still reported "voltou, mas lento" after the
`memory_limit` bump, confirming the underlying full-table-scan-times-6
cost is still there.

**Real fix, not yet done**: replace the "load everything once, filter 6x
in Blade" shape with either (a) server-side pagination + a `localizacao`
`WHERE` filter per tab (AJAX per-tab load, DataTables server-side mode),
or (b) at minimum `paginate()` instead of `get()` if all tabs must stay
client-side-filterable. Requires reworking the 6-tab UX in
`resources/views/pacotes/index.blade.php` (~1041 lines) — bigger risk
than the column-trim mitigation, deliberately deferred.

**Same anti-pattern found elsewhere, not yet triaged/fixed** (grep
`::with(\[` + `->get()` with no `select()`/`paginate()` in
`app/Http/Controllers/`):
- `RelatorioController.php:112` — `Pacote::with(['ocsPsa','tipoPacote'])->...->get()`, no select, no pagination.
- `ConfiguracoesController.php:339,400` — `Pacote::with(['ocsPsa','tipoPacote','tipoConta'])->...->get()`, no select.
- `PesquisaController.php:169-258` — builds a query with the same 4-relation eager load; one code path already uses `paginate(15)` (line 135, OK), but the path ending at `$query->get()` (line 258) does not.
- `PacoteController.php:16` (**singular**, not `PacotesController`) — a separate, smaller controller with the same `Pacote::with(['ocsPsa','tipoPacote','tipoConta'])->get()` shape. Worth checking whether this is dead code / a stray duplicate before touching it.
- `app/Http/Controllers/PacotesController.txt` and `PacotesController-bpk2.txt` — stray non-`.php` backup files sitting in the Controllers directory with the *pre-fix* buggy query baked in. Not routed (wrong extension), but confusing clutter next to the real controller; worth deleting or moving out of `app/Http/Controllers/` in a future cleanup pass.

## CI/CD notes

- **Self-hosted runner label**: both `cd-homolog.yml` and `cd-prod.yml`
  target `runs-on: [self-hosted, Linux, X64, pmed2-interno]`. The custom
  label `pmed2-interno` is required — a freshly registered runner without
  that label won't pick up these jobs (they'll sit "Waiting for a
  runner" forever). Always pass `--labels pmed2-interno` when
  reconfiguring.
- **GitHub's "Upcoming change to GitHub App installation token format"
  notice (longer `ghs_...` tokens, ~520 chars) does not affect this
  repo.** Checked (2026-07-09): the only place `secrets.GITHUB_TOKEN` is
  used is `docker-build.yml`'s registry login step, consumed opaquely by
  `docker/login-action`. No workflow, script, or app code in this repo
  parses, truncates, or length-validates a GitHub token. No action
  needed unless a future workflow starts doing manual token handling.
