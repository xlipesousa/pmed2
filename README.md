# 🏥 PMED 2.0

Sistema de Gestão e Controle de Faturas Hospitalares

[![Laravel](https://img.shields.io/badge/Laravel-12-red?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue?logo=php)](https://php.net)

---

## 📋 Sobre o Projeto

O **PMED 2.0** é o sistema de controle de pagamento de faturas hospitalares do **FUSEx**
(plano de saúde do Exército Brasileiro). Gerencia o ciclo de vida de **pacotes** de faturas
enviadas por prestadores credenciados, desde a entrada no protocolo até o arquivamento
final, passando por auditoria, glosas, recursos e pagamento.

Pense nele como contas a pagar B2B com um pipeline de auditoria no meio: nada é pago sem
passar por conferência e possível contestação.

**Documentação completa:** [`docs/Home.md`](docs/Home.md) — cofre Obsidian versionado com
domínio, arquitetura, decisões de infraestrutura (ADRs) e runbooks operacionais. Este README
é só a porta de entrada; para qualquer dúvida mais profunda, o cofre é a fonte da verdade.

### ✨ Principais Funcionalidades

- 📦 **Gestão Completa de Pacotes** — CRUD, movimentações, glosas, anulações
- 💰 **Mapas de Pagamento** — Criação, gestão e exportação
- 📊 **Relatórios Gerenciais** — Status, performance, glosas, financeiro
- 🔍 **Pesquisa Avançada** — Multicritério com exportação (PDF, Excel)
- 📈 **Dashboards e Gráficos** — KPIs, tendências e análises
- ⚙️ **Configurações** — OCS/PSA, tipos, usuários
- 🔐 **Controle de Acesso** — 8 perfis de usuário, mapeados às etapas do fluxo de auditoria
  (ver [`docs/00-projeto/Perfis e permissões.md`](docs/00-projeto/Perfis%20e%20permissões.md))

---

## 🚀 Como rodar

O ambiente de execução real (homologação e produção) é **Docker + GitHub Actions** — não há
mais instalação via script em servidor. Ver
[`docs/20-arquitetura/Stack e ambientes.md`](docs/20-arquitetura/Stack%20e%20ambientes.md) e
[`docs/30-operacao/Runbook de deploy.md`](docs/30-operacao/Runbook%20de%20deploy.md).

### Desenvolvimento local

```bash
git clone https://github.com/xlipesousa/pmed2.git
cd pmed2

composer install
npm install

cp .env.example .env
php artisan key:generate

mkdir -p .secrets
openssl rand -base64 24 | tr -d '/+=' | head -c 24 > .secrets/db_password
echo "" >> .secrets/db_password
openssl rand -base64 24 | tr -d '/+=' | head -c 24 > .secrets/db_root_password
echo "" >> .secrets/db_root_password
chmod 600 .secrets/db_password .secrets/db_root_password

npm run build
docker compose up -d --build   # 1ª build compila extensões PHP, leva alguns minutos
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --class=AdminUserSeeder --force
```

A skill `run-pmed2` (`.claude/skills/run-pmed2/`) tem o passo a passo completo, incluindo
troubleshooting e como dirigir a UI via navegador headless para verificação automatizada.

> [!IMPORTANT]
> A instalação cria um usuário administrador padrão. **A credencial não é publicada aqui** —
> confira a saída do `db:seed` ou o seeder `AdminUserSeeder`, e troque a senha assim que
> acessar. Nunca reutilize a credencial de desenvolvimento em homologação ou produção.

---

## 🏗️ Stack Tecnológica

### Backend
- **Framework:** Laravel 12, PHP 8.2+
- **Banco de Dados:** MySQL 8.0 (fora do container — ver
  [`docs/40-decisoes/ADR-01.md`](docs/40-decisoes/ADR-01.md))
- **Cache/Fila:** Redis
- **Template Admin:** AdminLTE 3

### Frontend
- **Template Engine:** Blade
- **CSS:** Bootstrap 5 + Tailwind 4 (parcial — ver dívida técnica abaixo)
- **Build Tool:** Vite

### Bibliotecas Principais
- `barryvdh/laravel-dompdf` — geração de PDFs
- `intervention/image` — processamento de imagens
- `laravel/sanctum` — instalado, não usado (não há API)

---

## 🔐 Perfis de Usuário

Cada estágio do fluxo de auditoria é também um perfil — não é uma hierarquia genérica de
permissões, é a modelagem de quem trabalha em cada etapa:

| Perfil | Descrição |
|--------|-----------|
| **Admin** | Acesso total ao sistema |
| **Auditor** | Visualização, sem poder de alterar |
| **Protocolo** | Entrada de pacotes |
| **Lisura** | Análise de conformidade e abertura de glosas |
| **SIRE** | Autorização de pagamentos |
| **Glosa** | Gestão de recursos e contestações |
| **Arquivo** | Arquivamento do pacote processado |
| **Pagamento** | Mapas de pagamento |

Detalhe importante para quem for mexer em autorização: a implementação real hoje é
majoritariamente checagens dentro dos controllers, não uma tabela de rotas auditável. Ver
[`docs/00-projeto/Perfis e permissões.md`](docs/00-projeto/Perfis%20e%20permissões.md).

## 🔄 Fluxo Operacional

```
Protocolo → Lisura → SIRE → Glosa → Arquivo → Arquivado
```

Simplificação didática — o SIRE é na verdade um ponto de ramificação com 7 casos. Fluxo real
e diagrama completo em
[`docs/10-dominio/Ciclo de vida do pacote.md`](docs/10-dominio/Ciclo%20de%20vida%20do%20pacote.md).

---

## 🧪 Testes

```bash
timeout 90 ./vendor/bin/phpunit --testdox
```

> [!WARNING]
> A suíte de testes está sendo reconstruída. Historicamente ela reportava verde sem cobrir
> nada de real — ver
> [`docs/20-arquitetura/Dívida técnica.md`](docs/20-arquitetura/Dívida%20técnica.md) (item
> 1). Não trate "os testes passam" como prova de nada até essa nota ser atualizada.

---

## 📁 Estrutura de Diretórios

```
pmed2/
├── app/                     # Controllers, Models, Helpers, Providers
├── database/migrations/     # Migrations do banco
├── resources/views/         # Views Blade
├── routes/web.php          # Rotas web
├── docker/, deploy/         # Dockerfile, composes de homolog/produção
├── .github/workflows/       # CI e deploy (GitHub Actions)
├── docs/                    # Cofre de documentação (fonte da verdade) — abra Home.md
├── specs/                   # Specs de mudanças estruturais em andamento
└── .claude/skills/          # Skills do projeto para trabalho assistido por IA
```

---

## 🤝 Contribuindo

```bash
git checkout -b feature/MinhaFeature
# ... trabalho, seguindo Conventional Commits ...
git push origin feature/MinhaFeature
# abrir Pull Request
```

Mudanças estruturais (refatoração, nova feature de peso) seguem a convenção de specs em
`specs/` — ver a skill `pmed2-spec`.

### Padrão de Commits

[Conventional Commits](https://www.conventionalcommits.org/): `feat:`, `fix:`, `docs:`,
`style:`, `refactor:`, `test:`, `chore:`.

---

## 👤 Autor

**Felipe Pedrosa** — [@xlipesousa](https://github.com/xlipesousa)

---

## 📞 Suporte

Para reportar bugs ou solicitar features, abra uma
[issue](https://github.com/xlipesousa/pmed2/issues).
