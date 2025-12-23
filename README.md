# 🏥 PMED 2.0

Sistema de Gestão e Controle de Faturas Hospitalares

[![Laravel](https://img.shields.io/badge/Laravel-12.8.1-red?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue?logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## 📋 Sobre o Projeto

O **PMED 2.0** é um sistema completo para gestão de faturas hospitalares de convênios médicos. Gerencia todo o fluxo de pacotes de faturas desde a entrada no protocolo até o arquivamento final, incluindo processos de glosas, recursos e pagamentos.

### ✨ Principais Funcionalidades

- 📦 **Gestão Completa de Pacotes** - CRUD, movimentações, glosas, anulações
- 💰 **Mapas de Pagamento** - Criação, gestão e exportação
- 📊 **Relatórios Gerenciais** - Status, performance, glosas, financeiro
- 🔍 **Pesquisa Avançada** - Multicritério com exportação (PDF, Excel)
- 📈 **Dashboards e Gráficos** - KPIs, tendências e análises
- ⚙️ **Configurações** - OCS/PSA, tipos, usuários
- 🔐 **Controle de Acesso** - 8 perfis diferentes com permissões granulares

---

## 🚀 Início Rápido

### Pré-requisitos

- PHP 8.2+
- MySQL 8.0+ ou MariaDB 10.6+
- Node.js 18+
- Composer 2.6+

### Instalação Automatizada

```bash
# Clone o repositório
git clone https://github.com/xlipesousa/pmed2.git
cd pmed2

# Execute o script de instalação (requer sudo)
sudo ./install.sh
```

O script `install.sh` irá:
- ✅ Instalar todas as dependências do sistema
- ✅ Configurar PHP, MySQL e Nginx
- ✅ Instalar dependências do Composer e NPM
- ✅ Configurar banco de dados
- ✅ Executar migrations
- ✅ Compilar assets
- ✅ Configurar permissões

### Instalação Manual

```bash
# 1. Clone e entre no diretório
git clone https://github.com/xlipesousa/pmed2.git
cd pmed2

# 2. Instale dependências
composer install
npm install

# 3. Configure o ambiente
cp .env.example .env
php artisan key:generate

# 4. Configure o banco de dados no .env
# DB_DATABASE=pmed2
# DB_USERNAME=seu_usuario
# DB_PASSWORD=sua_senha

# 5. Execute migrations
php artisan migrate

# 6. Compile assets
npm run build

# 7. Configure permissões
chmod -R 775 storage bootstrap/cache
```

---

## 📖 Documentação

- 📄 **[Relatório Completo do Sistema](relatorio.md)** - Documentação técnica detalhada
- 🔧 **[Requisitos e Dependências](requirements.txt)** - Lista completa de software necessário
- 🛠️ **[Script de Instalação](install.sh)** - Instalação automatizada
- 🔄 **[Script de Atualização](update.sh)** - Atualização do sistema

---

## 🏗️ Stack Tecnológica

### Backend
- **Framework:** Laravel 12.8.1
- **PHP:** 8.2+
- **Banco de Dados:** MySQL 8.0+ / MariaDB 10.6+
- **Template Admin:** AdminLTE 3.15

### Frontend
- **Template Engine:** Blade
- **CSS:** Bootstrap 5.2.3 + Tailwind CSS 4.0
- **Build Tool:** Vite 6.2.4
- **JavaScript:** Axios 1.8.2

### Bibliotecas Principais
- `barryvdh/laravel-dompdf` - Geração de PDFs
- `intervention/image` - Processamento de imagens
- `doctrine/dbal` - Manipulação de schemas
- `laravel/sanctum` - Autenticação API

---

## 📊 Estatísticas do Sistema

- **999** Pacotes gerenciados
- **26** Usuários ativos
- **22** Tabelas no banco de dados
- **8** Perfis de usuário
- **60+** Rotas implementadas
- **8** Controllers principais

---

## 🔐 Perfis de Usuário

| Perfil | Descrição |
|--------|-----------|
| **Admin** | Acesso total ao sistema |
| **Auditor** | Visualização sem modificação |
| **Protocolo** | Entrada de pacotes |
| **Lisura** | Análise e glosas |
| **SIRE** | Autorização de pagamentos |
| **Glosa** | Gestão de recursos |
| **Arquivo** | Arquivamento |
| **Pagamento** | Mapas de pagamento |

---

## 🔄 Fluxo Operacional

```
Protocolo → Lisura → SIRE → Glosa → Arquivo → Arquivado
```

---

## 🛠️ Comandos Úteis

### Desenvolvimento
```bash
# Iniciar servidor de desenvolvimento
php artisan serve

# Compilar assets em modo watch
npm run dev

# Executar migrations
php artisan migrate

# Limpar caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Produção
```bash
# Compilar assets para produção
npm run build

# Otimizar caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Modo manutenção
php artisan down
php artisan up
```

### Atualização
```bash
# Atualizar do repositório
./update.sh
```

---

## 📁 Estrutura de Diretórios

```
pmed2/
├── app/
│   ├── Http/Controllers/    # Controllers
│   ├── Models/              # Models Eloquent
│   ├── Helpers/             # Helpers customizados
│   └── Providers/           # Service Providers
├── database/
│   ├── migrations/          # Migrations do banco
│   └── seeders/            # Seeders
├── resources/
│   ├── views/              # Views Blade
│   ├── js/                 # JavaScript
│   └── sass/               # Estilos
├── routes/
│   └── web.php            # Rotas web
├── public/                # Assets públicos
├── storage/               # Storage e logs
├── install.sh            # Script de instalação
├── update.sh             # Script de atualização
├── requirements.txt      # Dependências
└── relatorio.md         # Documentação completa
```

---

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor:

1. Faça um Fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'feat: Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

### Padrão de Commits

Seguimos o [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` Nova funcionalidade
- `fix:` Correção de bug
- `docs:` Documentação
- `style:` Formatação
- `refactor:` Refatoração
- `test:` Testes
- `chore:` Manutenção

---

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

---

## 👤 Autor

**Felipe Pedrosa**

- GitHub: [@xlipesousa](https://github.com/xlipesousa)
- Email: xlipesousa@gmail.com

---

## 🙏 Agradecimentos

- Laravel Framework
- AdminLTE
- Comunidade Open Source

---

## 📞 Suporte

Para reportar bugs ou solicitar features, por favor abra uma [issue](https://github.com/xlipesousa/pmed2/issues).

---

**⭐ Se este projeto foi útil para você, considere dar uma estrela!**
