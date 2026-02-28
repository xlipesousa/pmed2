# PMED2 — Guia de Recuperação de Desastres (DR)

Guia definitivo para restaurar o ambiente em snapshot limpo e colocar a aplicação em produção LAB novamente via `CD por tag`, sem ajustes manuais fora do roteiro.

## Objetivo

Ao final deste procedimento:
- pipeline `CD` conclui com sucesso;
- aplicação responde `200` em `/health`;
- `/login` responde `200` ou `302`;
- sem erros de `APP_KEY`, permissões ou autenticação de backup no deploy.

---

## Premissas

- Máquinas envolvidas: `ubuntu-prod`, `ubuntu-runner`, `ubuntu-dev`.
- Usuário de deploy no prod: `admin21ct`.
- Host prod: `192.168.0.251`.
- Path da aplicação: `/var/www/pmed2`.

---

## 1) Recuperação do servidor (ubuntu-prod)

1. **[ubuntu-prod] Instalar stack base**
```bash
sudo apt update && sudo apt -y install nginx mysql-server php8.3-fpm php8.3-cli php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip unzip curl git
```

2. **[ubuntu-prod] Validar usuário de deploy/sudo**
```bash
id admin21ct && sudo -l | head -n 20
```

3. **[ubuntu-prod] Criar estrutura de releases/shared**
```bash
sudo mkdir -p /var/www/pmed2/{releases,shared/scripts,shared/storage,backups} && sudo chown -R admin21ct:www-data /var/www/pmed2 && sudo chmod -R 775 /var/www/pmed2
```

4. **[ubuntu-prod] Criar banco e usuário (snapshot-safe, sempre atualiza senha)**
```bash
sudo mysql <<'SQL'
CREATE DATABASE IF NOT EXISTS pmed2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'pmed2user'@'localhost' IDENTIFIED BY 'SUA_SENHA_FORTE_AQUI';
CREATE USER IF NOT EXISTS 'pmed2user'@'127.0.0.1' IDENTIFIED BY 'SUA_SENHA_FORTE_AQUI';
ALTER USER 'pmed2user'@'localhost' IDENTIFIED BY 'SUA_SENHA_FORTE_AQUI';
ALTER USER 'pmed2user'@'127.0.0.1' IDENTIFIED BY 'SUA_SENHA_FORTE_AQUI';
GRANT ALL PRIVILEGES ON pmed2.* TO 'pmed2user'@'localhost';
GRANT ALL PRIVILEGES ON pmed2.* TO 'pmed2user'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL
```

5. **[ubuntu-prod] Testar login MySQL da aplicação (senha com especiais suportada)**
```bash
read -rsp "Senha do pmed2user: " DB_PASS && echo && mysql -h127.0.0.1 -upmed2user -p"$DB_PASS" -e 'SELECT 1' pmed2
```

6. **[ubuntu-prod] Criar `.env` compartilhado com APP_KEY real e senha segura**
```bash
read -rsp "Senha do banco (pmed2user): " DB_PASS && echo && APP_KEY_VALUE="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')" && sudo tee /var/www/pmed2/shared/.env >/dev/null <<EOF
APP_NAME=PMED2
APP_ENV=production
APP_KEY=${APP_KEY_VALUE}
APP_DEBUG=false
APP_URL=http://192.168.0.251
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
sudo chown admin21ct:www-data /var/www/pmed2/shared/.env && sudo chmod 664 /var/www/pmed2/shared/.env && unset DB_PASS
```

7. **[ubuntu-prod] Validar formato/tamanho da APP_KEY (32 bytes)**
```bash
APP_KEY_LINE="$(grep -E '^APP_KEY=' /var/www/pmed2/shared/.env | tail -n1 | cut -d'=' -f2-)" && APP_KEY_VALUE="$APP_KEY_LINE" php -r '$k=getenv("APP_KEY_VALUE"); if(!str_starts_with($k,"base64:")){exit(1);} $r=base64_decode(substr($k,7),true); if($r===false||strlen($r)!==32){exit(1);} echo "APP_KEY_OK\n";'
```

8. **[ubuntu-prod] Configurar Nginx Laravel (`try_files`)**
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
```

9. **[ubuntu-prod] Ativar site e serviços**
```bash
sudo ln -sfn /etc/nginx/sites-available/pmed2 /etc/nginx/sites-enabled/pmed2 && sudo rm -f /etc/nginx/sites-enabled/default && sudo nginx -t && sudo systemctl enable --now nginx php8.3-fpm && sudo systemctl reload nginx
```

10. **[ubuntu-prod] Garantir hardening de sudo para reload (com fallback se script não existir)**
```bash
if [ -f /home/admin21ct/pmed2/scripts/hardening_sudo_phpfpm.sh ]; then sudo bash /home/admin21ct/pmed2/scripts/hardening_sudo_phpfpm.sh; else sudo tee /usr/local/sbin/hardening_sudo_phpfpm.sh >/dev/null <<'EOF'
#!/usr/bin/env bash
set -euo pipefail
SUDOERS_FILE="/etc/sudoers.d/pmed2-deploy-phpfpm"
DEPLOY_USER="${1:-admin21ct}"
PHP_FPM_SERVICE="${2:-php8.3-fpm}"
cat > "${SUDOERS_FILE}" <<EOT
${DEPLOY_USER} ALL=(root) NOPASSWD:/usr/bin/systemctl reload ${PHP_FPM_SERVICE}
EOT
chmod 440 "${SUDOERS_FILE}"
visudo -cf "${SUDOERS_FILE}"
EOF
sudo chmod +x /usr/local/sbin/hardening_sudo_phpfpm.sh && sudo /usr/local/sbin/hardening_sudo_phpfpm.sh; fi
```

11. **[ubuntu-prod] Pré-ajuste de permissão runtime (anti-500)**
```bash
APP=/var/www/pmed2; sudo mkdir -p $APP/shared/storage/{logs,app/public,framework/{views,cache,sessions,testing}}; sudo touch $APP/shared/storage/logs/laravel.log; sudo chown -R admin21ct:www-data $APP/shared/storage; sudo find $APP/shared/storage -type d -exec chmod 2775 {} +; sudo find $APP/shared/storage -type f -exec chmod 664 {} +
```

---

## 2) Pré-check de conectividade (ubuntu-runner)

12. **[ubuntu-runner] Confirmar chave privada de CD**
```bash
ls -l ~/.ssh/pmed2_cd
```

13. **[ubuntu-runner] Testar SSH sem interação para o prod**
```bash
ssh -i ~/.ssh/pmed2_cd -o BatchMode=yes admin21ct@192.168.0.251 "hostname && whoami"
```

14. **[ubuntu-runner] Confirmar sudo sem prompt para reload do php-fpm**
```bash
ssh -i ~/.ssh/pmed2_cd admin21ct@192.168.0.251 "sudo -n systemctl reload php8.3-fpm && echo SUDO_OK"
```

---

## 3) Disparo do recovery via CD (ubuntu-dev)

15. **[ubuntu-dev] Validar deploy script definitivo na cópia local**
```bash
cd /home/admin21ct/pmed2 && grep -n "healthcheck falhou após deploy\|Rollback automático aplicado\|APP_KEY com formato/tamanho inválido" scripts/deploy.sh
```

16. **[ubuntu-dev] Atualizar branch local**
```bash
cd /home/admin21ct/pmed2 && git checkout main && git pull
```

17. **[ubuntu-dev] Criar próxima tag sem colisão e enviar**
```bash
cd /home/admin21ct/pmed2 && LAST_TAG=$(git tag -l 'v*.*.*' --sort=-v:refname | head -n1) && if [ -z "$LAST_TAG" ]; then TAG=v0.1.0; else TAG=$(echo "$LAST_TAG" | awk -F. '{gsub(/^v/,"",$1); printf "v%d.%d.%d",$1,$2,$3+1}'); fi && git tag "$TAG" && git push origin "$TAG" && echo "Tag criada: $TAG"
```

---

## 4) Validação de recuperação (ubuntu-prod)

18. **[ubuntu-prod] Confirmar release ativa**
```bash
readlink -f /var/www/pmed2/current
```

19. **[ubuntu-prod] Verificar `/health`**
```bash
curl -fsS http://127.0.0.1/health
```

20. **[ubuntu-prod] Verificar `/login`**
```bash
curl -I http://127.0.0.1/login
```

21. **[ubuntu-prod] Ler logs Laravel (arquivo único ou diário)**
```bash
tail -n 120 /var/www/pmed2/shared/storage/logs/laravel*.log 2>/dev/null
```

22. **[ubuntu-prod] Buscar erros críticos conhecidos**
```bash
tail -n 200 /var/www/pmed2/shared/storage/logs/laravel*.log 2>/dev/null | grep -Ei "Permission denied|Unsupported cipher|incorrect key length|No application encryption key|local.ERROR|production.ERROR" || echo "SEM_ERRO_CRITICO_CONHECIDO"
```

---

## 5) Runbook de contingência do erro 500 (se necessário)

23. **[ubuntu-prod] Diagnóstico rápido**
```bash
APP=/var/www/pmed2; CUR=$(readlink -f $APP/current); echo "CUR=$CUR"; sudo journalctl -u php8.3-fpm -n 80 --no-pager; sudo nginx -t; tail -n 120 $APP/shared/storage/logs/laravel*.log 2>/dev/null
```

24. **[ubuntu-prod] Reaplicar permissão runtime completa**
```bash
APP=/var/www/pmed2; CUR=$(readlink -f $APP/current); sudo mkdir -p $APP/shared/storage/{logs,app/public,framework/{views,cache,sessions,testing}} $CUR/bootstrap/cache; sudo touch $APP/shared/storage/logs/laravel.log; sudo chown -R admin21ct:www-data $APP/shared/storage $CUR/bootstrap/cache; sudo find $APP/shared/storage $CUR/bootstrap/cache -type d -exec chmod 2775 {} +; sudo find $APP/shared/storage $CUR/bootstrap/cache -type f -exec chmod 664 {} +
```

25. **[ubuntu-prod] Recarregar serviços e revalidar**
```bash
sudo systemctl reload php8.3-fpm && sudo systemctl reload nginx && curl -fsS http://127.0.0.1/health && curl -I http://127.0.0.1/login
```

26. **[ubuntu-prod] Confirmar ausência de erro de permissão**
```bash
tail -n 200 /var/www/pmed2/shared/storage/logs/laravel*.log 2>/dev/null | grep -i "Permission denied" && echo "ERRO" || echo "OK_sem_permission_denied"
```

---

## 6) Critério de recuperação concluída

Recuperação aprovada quando todos os itens forem verdadeiros:

1. `CD` por tag conclui com sucesso.
2. `current` aponta para release recém-gerada.
3. `/health` retorna `200`.
4. `/login` retorna `200` ou `302`.
5. Logs sem `Permission denied`, sem erro de `APP_KEY` e sem `500` recorrente.
