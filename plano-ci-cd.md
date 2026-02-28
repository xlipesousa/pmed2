# Plano CI/CD — Guia Detalhado para Iniciantes (GitHub)

Este documento foi escrito para execução por pessoa leiga, em ambiente limpo.
Siga os passos exatamente na ordem.

## 0) Cenário e objetivo

### Máquinas
- `ubuntu-dev` (seu PC/local, com VPN ativa)
- `ubuntu-runner` (`10.122.8.13`)
- `ubuntu-homolog` (`10.122.8.14`)
- `ubuntu-prod` (`10.122.8.15`)

### Objetivo final
1. Push no GitHub dispara CI.
2. Tag `vX.Y.Z` faz deploy automático em homolog.
3. Após validação, produção sobe por aprovação manual.

---

## 1) Convenções deste guia

- Sempre que aparecer **[MÁQUINA]**, rode o comando nessa máquina.
- Copie/cole os comandos sem alterar, exceto onde indicado.
- Se um passo falhar, **pare e corrija antes de seguir**.

Variáveis usadas neste guia:
- Usuário SSH: `admin21ct`
- Pasta da app: `/var/www/pmed2`
- Banco: `pmed2`
- Usuário DB: `pmed2user`

---

## 2) Pré-check de conectividade (obrigatório)

## 2.1 [ubuntu-dev]

1. Testar VPN/rede:
```bash
ping -c 3 10.122.8.13
ping -c 3 10.122.8.14
ping -c 3 10.122.8.15
```

2. Testar SSH:
```bash
ssh admin21ct@10.122.8.13 "hostname && whoami"
ssh admin21ct@10.122.8.14 "hostname && whoami"
ssh admin21ct@10.122.8.15 "hostname && whoami"
```

Resultado esperado: hostname e usuário retornam sem erro.

---

## 3) Preparar o runner (10.122.8.13)

## 3.1 [ubuntu-runner]

1. Instalar dependências:
```bash
sudo apt update && sudo apt -y install git curl unzip zip openssh-client ca-certificates php8.3-cli composer
```

2. Criar diretório do runner:
```bash
mkdir -p ~/actions-runner && cd ~/actions-runner
```

3. No GitHub (web):
- Repositório `pmed2` -> `Settings` -> `Actions` -> `Runners` -> `New self-hosted runner`.
- Escolha Linux x64.
- Copie os comandos mostrados e execute no `ubuntu-runner`.

4. Quando pedir labels no `config.sh`, use:
```text
self-hosted,Linux,X64,pmed2-interno
```

5. Instalar como serviço:
```bash
cd ~/actions-runner
sudo ./svc.sh install
sudo ./svc.sh start
sudo ./svc.sh status
```

Resultado esperado: status `active` e runner online no GitHub.

6. Validar pré-requisitos do runner (igual ao workflow):
```bash
command -v php
command -v composer
command -v zip
command -v ssh
command -v scp
php -v
composer --version
```

Regra importante:
- em runner self-hosted, evite actions que executam `sudo` e pedem senha (não há terminal interativo no job);
- o runner deve ter ferramentas base já instaladas no bootstrap.

---

## 4) Preparar homolog (10.122.8.14)

## 4.1 [ubuntu-homolog] Instalar stack
```bash
sudo apt update && sudo apt -y install nginx mysql-server php8.3-fpm php8.3-cli php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip unzip curl git
```

## 4.2 [ubuntu-homolog] Criar estrutura de deploy
```bash
sudo mkdir -p /var/www/pmed2/{releases,shared/scripts,shared/storage,backups}
sudo chown -R admin21ct:www-data /var/www/pmed2
sudo chmod -R 775 /var/www/pmed2
```

## 4.3 [ubuntu-homolog] Criar banco e usuário

```bash
read -rsp "Senha do banco HOMOLOG (pmed2user): " DB_PASS && echo
sudo mysql <<SQL
CREATE DATABASE IF NOT EXISTS pmed2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'pmed2user'@'localhost' IDENTIFIED BY '${DB_PASS}';
CREATE USER IF NOT EXISTS 'pmed2user'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';
ALTER USER 'pmed2user'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER 'pmed2user'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON pmed2.* TO 'pmed2user'@'localhost';
GRANT ALL PRIVILEGES ON pmed2.* TO 'pmed2user'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL
mysql -h127.0.0.1 -upmed2user -p"$DB_PASS" -e 'SELECT 1' pmed2
```

Resultado esperado: comando `SELECT 1` retorna sucesso.

## 4.4 [ubuntu-homolog] Criar `.env` compartilhado

```bash
APP_KEY_VALUE="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')"
sudo tee /var/www/pmed2/shared/.env >/dev/null <<EOF
APP_NAME=PMED2
APP_ENV=homolog
APP_KEY=${APP_KEY_VALUE}
APP_DEBUG=false
APP_URL=http://10.122.8.14
LOG_CHANNEL=daily
LOG_LEVEL=debug
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pmed2
DB_USERNAME=pmed2user
DB_PASSWORD=${DB_PASS}
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
EOF
sudo chown admin21ct:www-data /var/www/pmed2/shared/.env
sudo chmod 664 /var/www/pmed2/shared/.env
unset DB_PASS
```

## 4.5 [ubuntu-homolog] Configurar Nginx Laravel

```bash
sudo tee /etc/nginx/sites-available/pmed2 >/dev/null <<'EOF'
server {
	listen 80;
	listen [::]:80;
	server_name _;
	root /var/www/pmed2/current/public;
	index index.php index.html;

	location / {
		try_files $uri $uri/ /index.php?$query_string;
	}

	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
		fastcgi_pass unix:/run/php/php8.3-fpm.sock;
		fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
		include fastcgi_params;
	}

	location ~ /\.(?!well-known).* {
		deny all;
	}
}
EOF

sudo ln -sfn /etc/nginx/sites-available/pmed2 /etc/nginx/sites-enabled/pmed2
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl enable --now nginx php8.3-fpm
sudo systemctl reload nginx
```

## 4.6 [ubuntu-homolog] Configurar sudo sem senha para reload php-fpm

```bash
sudo tee /etc/sudoers.d/pmed2-deploy-phpfpm >/dev/null <<'EOF'
admin21ct ALL=(root) NOPASSWD:/usr/bin/systemctl reload php8.3-fpm
EOF
sudo chmod 440 /etc/sudoers.d/pmed2-deploy-phpfpm
sudo visudo -cf /etc/sudoers.d/pmed2-deploy-phpfpm
sudo -n systemctl reload php8.3-fpm && echo SUDO_OK
```

## 4.7 [ubuntu-homolog] Pré-ajuste de permissão runtime

```bash
APP=/var/www/pmed2
sudo mkdir -p $APP/shared/storage/{logs,app/public,framework/{views,cache,sessions,testing}}
sudo touch $APP/shared/storage/logs/laravel.log
sudo chown -R admin21ct:www-data $APP/shared/storage
sudo find $APP/shared/storage -type d -exec chmod 2775 {} +
sudo find $APP/shared/storage -type f -exec chmod 664 {} +
```

---

## 5) Preparar produção (10.122.8.15)

## 5.1 [ubuntu-prod]

Repita exatamente os passos da seção 4 (homolog), com estas diferenças:
- `APP_ENV=production`
- `APP_URL` de produção
- senha de banco de produção (não reutilizar obrigatoriamente a de homolog)

Dica: execute e valide cada bloco igual ao homolog.

---

## 6) Criar chaves SSH para deploy (runner -> homolog/prod)

## 6.1 [ubuntu-runner] Gerar par de chaves

```bash
mkdir -p ~/.ssh && chmod 700 ~/.ssh
ssh-keygen -t ed25519 -f ~/.ssh/pmed2_homolog -N ""
ssh-keygen -t ed25519 -f ~/.ssh/pmed2_prod -N ""
```

## 6.2 [ubuntu-runner] Ver conteúdo das chaves públicas

```bash
cat ~/.ssh/pmed2_homolog.pub
cat ~/.ssh/pmed2_prod.pub
```

## 6.3 [ubuntu-homolog] Autorizar chave homolog

Cole a chave `.pub` de homolog em `~admin21ct/.ssh/authorized_keys`.

Comando auxiliar:
```bash
mkdir -p ~/.ssh && chmod 700 ~/.ssh
touch ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys
```

## 6.4 [ubuntu-prod] Autorizar chave prod

Repita o mesmo processo com a chave `.pub` de produção.

## 6.5 [ubuntu-runner] Testes SSH sem senha

```bash
ssh -i ~/.ssh/pmed2_homolog -o BatchMode=yes admin21ct@10.122.8.14 "hostname && whoami"
ssh -i ~/.ssh/pmed2_prod -o BatchMode=yes admin21ct@10.122.8.15 "hostname && whoami"
```

---

## 7) Configurar GitHub (repositório)

## 7.1 Criar Environments

No GitHub do repositório:
- `Settings` -> `Environments`
- criar `homolog`
- criar `production` e marcar required reviewers

## 7.2 Cadastrar Secrets

Em `Settings` -> `Secrets and variables` -> `Actions`:

### Segredos de homolog
- `PMED2_HOM_SSH_HOST` = `10.122.8.14`
- `PMED2_HOM_SSH_USER` = `admin21ct`
- `PMED2_HOM_SSH_KEY` = conteúdo do arquivo privado `~/.ssh/pmed2_homolog` (do runner)

### Segredos de produção
- `PMED2_PROD_SSH_HOST` = `10.122.8.15`
- `PMED2_PROD_SSH_USER` = `admin21ct`
- `PMED2_PROD_SSH_KEY` = conteúdo do arquivo privado `~/.ssh/pmed2_prod` (do runner)

Importante: colar chave privada completa (incluindo BEGIN/END).

---

## 8) Workflows no repositório

Nesta etapa, o repositório deve ficar exatamente assim:

1. `ci.yml` (já existente) para CI em `main` e PR.
2. `cd-homolog.yml` para deploy automático em homolog via tag `v*.*.*`.
3. `cd-prod.yml` para deploy manual em produção via `workflow_dispatch`.
4. `cd.yml` antigo removido (para não disparar deploy no fluxo antigo).

Checklist rápido (rode em `ubuntu-dev` dentro do repositório):
```bash
ls -1 .github/workflows
```

Resultado esperado:
- existe `ci.yml`
- existe `cd-homolog.yml`
- existe `cd-prod.yml`
- não existe `cd.yml`

Critérios técnicos dos dois CDs:
- `runs-on: [self-hosted, Linux, X64, pmed2-interno]`
- uso de secrets de homolog/prod separados
- upload de artefato + scripts
- auditoria de hash dos scripts remotos
- execução de `deploy.sh`
- healthcheck pós-deploy (`/health` e `/login`)

---

## 9) Primeiro deploy em homolog (teste de pipeline)

## 9.1 [ubuntu-dev] Atualizar repositório

```bash
cd /home/admin21ct/pmed2
git checkout main
git pull
```

## 9.2 [ubuntu-dev] Criar tag nova sem colisão

```bash
cd /home/admin21ct/pmed2
LAST_TAG=$(git tag -l 'v*.*.*' --sort=-v:refname | head -n1)
if [ -z "$LAST_TAG" ]; then TAG=v0.1.0; else TAG=$(echo "$LAST_TAG" | awk -F. '{gsub(/^v/,"",$1); printf "v%d.%d.%d",$1,$2,$3+1}'); fi
git tag "$TAG"
git push origin "$TAG"
echo "Tag criada: $TAG"
```

## 9.3 [GitHub Web] Acompanhar workflow de homolog

`Actions` -> workflow `cd-homolog` -> verificar status verde.

## 9.4 [ubuntu-homolog] Validar aplicação no ar

```bash
readlink -f /var/www/pmed2/current
curl -fsS http://127.0.0.1/health
curl -I http://127.0.0.1/login
tail -n 120 /var/www/pmed2/shared/storage/logs/laravel*.log 2>/dev/null
```

Resultado esperado:
- `/health` responde 200
- `/login` responde 200/302

---

## 10) Promoção para produção

## 10.1 [GitHub Web] Aprovar produção

No workflow de produção (`cd-prod`):
- escolher a tag aprovada em homolog
- executar manualmente (`Run workflow`)
- aprovar environment `production` se solicitado

## 10.2 [ubuntu-prod] Validar produção

```bash
readlink -f /var/www/pmed2/current
curl -fsS http://127.0.0.1/health
curl -I http://127.0.0.1/login
tail -n 120 /var/www/pmed2/shared/storage/logs/laravel*.log 2>/dev/null
```

---

## 11) Troubleshooting rápido (erros mais comuns)

### Erro SSH no workflow (`Permission denied (publickey,password)`)
1. conferir se a `.pub` correta está no `authorized_keys` do alvo
2. conferir se o secret tem a chave privada correspondente
3. testar local no runner com `ssh -i ... -o BatchMode=yes`

### Erro MySQL no backup/deploy
1. rodar `ALTER USER` novamente
2. validar senha com `mysql -h127.0.0.1 -upmed2user -p"..." -e 'SELECT 1' pmed2`

### Erro 500 após deploy
1. ajustar permissões de `storage` e `bootstrap/cache`
2. validar `APP_KEY` do `.env`
3. recarregar `php8.3-fpm` e `nginx`

---

## 12) Critérios de sucesso da implantação

1. Runner online e estável no GitHub.
2. CI rodando em push/PR.
3. Tag faz deploy em homolog automaticamente.
4. Produção sobe apenas com aprovação manual.
5. Homolog e produção respondem `/health` com sucesso.

---

## 13) Próxima evolução

Quando quiser migrar para GitLab, mantenha:
- mesma topologia de VMs;
- mesmos scripts de deploy/rollback;
- mesma política de aprovação para produção.

Troca apenas o orquestrador da pipeline.
