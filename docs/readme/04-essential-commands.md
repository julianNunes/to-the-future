# ⚡ Comandos Essenciais - To The Future

## 🚀 Script Principal - start.sh

### Comandos de Desenvolvimento

```bash
# Iniciar ambiente completo
./start.sh dev

# Parar todos os containers
./start.sh stop

# Ver logs em tempo real
./start.sh logs

# Verificar status dos containers
./start.sh status

# Verificar configuração do ambiente
./start.sh check
```

### Comandos de Setup

```bash
# Setup completo para novos desenvolvedores
./start.sh setup

# Instalar apenas extensões VS Code
./start.sh install-extensions

# Verificar se ambiente está pronto
./start.sh check-php
```

### Comandos de Backup/Restore

```bash
# Restore completo de backup (arquivo na raiz)
./start.sh full-restore backup.tar.gz

# Restore simples (apenas dados)
./start.sh simple-restore backup.tar.gz

# Copiar dados do restore para desenvolvimento
./start.sh copy-to-dev
```

### Comandos de Manutenção

```bash
# Limpeza completa (remove containers e volumes não utilizados)
./start.sh clean

# Corrigir permissões do Laravel
./start.sh fix-permissions
```

## 🐘 Comandos PHP/Laravel

### PHP via Container

```bash
# Versão do PHP
./scripts/php.sh -v

# Módulos carregados
./scripts/php.sh -m

# Executar script PHP
./scripts/php.sh meu-script.php

# Abrir REPL PHP
./scripts/php.sh -a
```

### Laravel Artisan

```bash
# ===== MIGRATIONS =====
./scripts/artisan.sh migrate                 # Executar migrations
./scripts/artisan.sh migrate:rollback        # Reverter última migration
./scripts/artisan.sh migrate:rollback --step=3  # Reverter 3 migrations
./scripts/artisan.sh migrate:fresh           # Limpar e recriar BD
./scripts/artisan.sh migrate:refresh         # Rollback + migrate
./scripts/artisan.sh migrate:status          # Status das migrations

# ===== GERADORES =====
./scripts/artisan.sh make:model Usuario      # Criar model
./scripts/artisan.sh make:controller UsuarioController  # Criar controller
./scripts/artisan.sh make:middleware Auth    # Criar middleware
./scripts/artisan.sh make:migration create_users_table  # Criar migration
./scripts/artisan.sh make:seeder UsersSeeder # Criar seeder
./scripts/artisan.sh make:factory UserFactory # Criar factory
./scripts/artisan.sh make:request UserRequest # Criar form request

# ===== CACHE =====
./scripts/artisan.sh cache:clear             # Limpar cache aplicação
./scripts/artisan.sh config:clear            # Limpar cache configuração
./scripts/artisan.sh view:clear              # Limpar cache views
./scripts/artisan.sh route:clear             # Limpar cache rotas
./scripts/artisan.sh clear-compiled          # Limpar compiled
./scripts/artisan.sh optimize                # Otimizar aplicação

# ===== FILAS/JOBS =====
./scripts/artisan.sh queue:work              # Processar filas
./scripts/artisan.sh queue:listen            # Escutar filas
./scripts/artisan.sh queue:restart           # Restart workers
./scripts/artisan.sh queue:failed            # Ver jobs falhou
./scripts/artisan.sh queue:retry all         # Retentar jobs

# ===== INFORMAÇÕES =====
./scripts/artisan.sh route:list              # Listar todas as rotas
./scripts/artisan.sh route:list --name=user  # Filtrar rotas
./scripts/artisan.sh tinker                  # REPL Laravel
./scripts/artisan.sh about                   # Info da aplicação
./scripts/artisan.sh env                     # Verificar ambiente

# ===== STORAGE =====
./scripts/artisan.sh storage:link            # Criar link simbólico
```

### Comandos Úteis Tinker

```bash
./scripts/artisan.sh tinker

# Dentro do tinker:
User::count()                    # Contar usuários
User::find(1)                    # Buscar usuário por ID
App\Models\User::factory()->create()  # Criar usuário fake
DB::table('users')->get()        # Query direta
cache()->flush()                 # Limpar cache
config('app.name')               # Ver configuração
```

## 📦 Gerenciamento de Dependências

### Composer (PHP)

```bash
# ===== INSTALAÇÃO =====
./scripts/composer.sh install              # Instalar dependências
./scripts/composer.sh install --no-dev     # Sem dependências de dev
./scripts/composer.sh update               # Atualizar dependências
./scripts/composer.sh update vendor/package # Atualizar pacote específico

# ===== ADICIONAR PACOTES =====
./scripts/composer.sh require monolog/monolog  # Adicionar dependência
./scripts/composer.sh require --dev phpunit/phpunit  # Dev dependency
./scripts/composer.sh remove vendor/package    # Remover pacote

# ===== INFORMAÇÕES =====
./scripts/composer.sh show                 # Listar pacotes instalados
./scripts/composer.sh show vendor/package  # Info do pacote
./scripts/composer.sh outdated             # Pacotes desatualizados
./scripts/composer.sh validate             # Validar composer.json

# ===== OTIMIZAÇÃO =====
./scripts/composer.sh dump-autoload        # Recriar autoloader
./scripts/composer.sh dump-autoload --optimize  # Otimizar autoloader
./scripts/composer.sh clear-cache          # Limpar cache
```

### NPM (JavaScript)

```bash
# ===== INSTALAÇÃO =====
./scripts/npm.sh install                   # Instalar dependências
./scripts/npm.sh install --production      # Apenas produção
./scripts/npm.sh update                    # Atualizar dependências

# ===== ADICIONAR PACOTES =====
./scripts/npm.sh install lodash            # Adicionar dependência
./scripts/npm.sh install --save-dev eslint # Dev dependency
./scripts/npm.sh uninstall lodash          # Remover pacote

# ===== BUILD =====
./scripts/npm.sh run dev                   # Build desenvolvimento
./scripts/npm.sh run production            # Build produção
./scripts/npm.sh run watch                 # Watch mode (auto-rebuild)
./scripts/npm.sh run hot                   # Hot module replacement

# ===== INFORMAÇÕES =====
./scripts/npm.sh list                      # Listar pacotes
./scripts/npm.sh outdated                  # Pacotes desatualizados
./scripts/npm.sh audit                     # Verificar vulnerabilidades
./scripts/npm.sh audit fix                 # Corrigir vulnerabilidades
```

## 🐳 Comandos Docker

### Gerenciamento de Containers

```bash
# Status dos containers
docker compose ps

# Logs específicos
docker compose logs app          # Logs do PHP
docker compose logs nginx        # Logs do Nginx
docker compose logs mysql        # Logs do MySQL
docker compose logs -f app       # Follow logs em tempo real

# Entrar em containers
docker compose exec app bash     # Shell no container PHP
docker compose exec mysql bash   # Shell no container MySQL
docker compose exec app php artisan tinker  # Direto no tinker

# Restart containers
docker compose restart app       # Restart apenas o PHP
docker compose restart           # Restart todos

# Build e rebuild
docker compose build             # Build todas as imagens
docker compose build --no-cache app  # Rebuild sem cache
docker compose up --build        # Up com rebuild
```

### Monitoramento

```bash
# Recursos utilizados
docker stats                     # Recursos em tempo real
docker stats --no-stream        # Snapshot dos recursos

# Informações do sistema
docker system df                 # Uso de espaço em disco
docker system info              # Info geral do Docker
docker compose config           # Validar docker-compose.yml

# Volumes
docker volume ls                 # Listar volumes
docker volume inspect mysql-data # Inspecionar volume específico
```

## 🗄️ Comandos de Banco de Dados

### MySQL via Container

```bash
# Conectar ao MySQL
docker compose exec mysql mysql -u user -puser laravel

# Backup do banco
docker compose exec mysql mysqldump -u user -puser laravel > backup.sql

# Restore do banco
docker compose exec -T mysql mysql -u user -puser laravel < backup.sql

# Ver logs do MySQL
docker compose logs mysql
```

### Comandos SQL Úteis

```sql
-- Dentro do MySQL
USE laravel;
SHOW TABLES;
DESCRIBE users;
SELECT COUNT(*) FROM users;
SHOW PROCESSLIST;
SHOW STATUS;
```

## 🔧 Formatação e Qualidade de Código

### PHP CS Fixer

```bash
# Formatar todo o código
./scripts/php-cs-fixer.sh fix

# Apenas verificar (dry run)
./scripts/php-cs-fixer.sh fix --dry-run

# Formatar pasta específica
./scripts/php-cs-fixer.sh fix app/

# Formatar arquivo específico
./scripts/php-cs-fixer.sh fix app/Models/User.php

# Ver diferenças que seriam aplicadas
./scripts/php-cs-fixer.sh fix --diff --dry-run
```

### Verificação de Código

```bash
# Verificar sintaxe PHP
./scripts/php.sh -l app/Models/User.php

# Executar testes
./scripts/php.sh vendor/bin/phpunit

# Executar testes específicos
./scripts/php.sh vendor/bin/phpunit tests/Feature/ExampleTest.php

# Executar com coverage (se configurado)
./scripts/php.sh vendor/bin/phpunit --coverage-html coverage/
```

## 📁 Comandos de Arquivos e Permissões

### Permissões Laravel

```bash
# Corrigir permissões automaticamente
./start.sh fix-permissions

# Manualmente (se necessário)
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### Limpeza de Arquivos

```bash
# Limpar logs Laravel
./scripts/artisan.sh log:clear

# Limpar arquivos temporários
docker compose exec app find storage/logs -name "*.log" -type f -delete

# Limpar cache de sessões
docker compose exec app rm -rf storage/framework/sessions/*
```

## 🔍 Debug e Troubleshooting

### Verificação de Ambiente

```bash
# Verificar se tudo está funcionando
./start.sh check

# Verificar ferramentas PHP
./start.sh check-php

# Info completa do PHP
./scripts/php.sh -i

# Testar conexão com banco
./scripts/artisan.sh migrate:status
```

### Logs e Debug

```bash
# Logs Laravel em tempo real
tail -f storage/logs/laravel.log

# Logs via Docker
docker compose logs -f app

# Debug de rotas
./scripts/artisan.sh route:list | grep "user"

# Debug de configuração
./scripts/artisan.sh config:show database.connections.mysql
```

### Comandos de Emergência

```bash
# Parar tudo e limpar
./start.sh stop
./start.sh clean

# Restart completo
./start.sh stop
./start.sh dev

# Reset completo do banco (CUIDADO!)
./scripts/artisan.sh migrate:fresh --seed

# Rebuild completo dos containers
docker compose down
docker compose build --no-cache
docker compose up -d
```

## 📖 Comandos de Documentação

### Gerar Documentação

```bash
# Ver todas as rotas documentadas
./scripts/artisan.sh route:list --columns=Method,URI,Name,Action

# Gerar documentação da API (se configurado)
./scripts/artisan.sh api:docs

# Listar todos os comandos artisan
./scripts/artisan.sh list

# Ajuda de comando específico
./scripts/artisan.sh help migrate
```

## ⚡ Shortcuts e Aliases Úteis

### Adicionar ao .bashrc/.zshrc

```bash
# Aliases para o projeto
alias ttf='cd /caminho/para/to-the-future'
alias ttfstart='./start.sh dev'
alias ttfstop='./start.sh stop'
alias ttflogs='./start.sh logs'
alias ttfartisan='./scripts/artisan.sh'
alias ttfcomposer='./scripts/composer.sh'
alias ttfnpm='./scripts/npm.sh'
alias nvtinker='./scripts/artisan.sh tinker'
alias nvmigrate='./scripts/artisan.sh migrate'
```

### VS Code Tasks Shortcuts

```
Ctrl+Shift+P → "Tasks: Run Task" → selecionar task
F5 → Start debugging (Xdebug)
Ctrl+` → Abrir terminal
Ctrl+Shift+` → Novo terminal
```

## 🧠 IDE Helper / IntelliSense

### Melhorar IntelliSense (sem poluir models)

```bash
# Gerar todos os arquivos helper (recomendado)
./start.sh ide-helper

# Ou usar script dedicado
./scripts/ide-helper.sh all

# Apenas models (após mudanças nos models)
./start.sh ide-models
./scripts/ide-helper.sh models

# Apenas facades
./start.sh ide-facades 
./scripts/ide-helper.sh facades

# Limpar arquivos helper
./scripts/ide-helper.sh clean
```

### Arquivos Gerados

- **`_ide_helper.php`** - Facades e helpers gerais do Laravel
- **`_ide_helper_models.php`** - Propriedades dos models (sem modificar originais)
- **`.phpstorm.meta.php`** - Meta informações para PHPStorm

### Quando Executar

```bash
# Setup inicial (uma vez)
./start.sh ide-helper

# Após criar/modificar models
./start.sh ide-models

# Após instalar packages que adicionam facades
./start.sh ide-facades

# ⚠️ Sempre reiniciar VS Code após gerar
```

### Configuração Otimizada

```php
// config/ide-helper.php (já configurado)
'write_model_magic_where' => false,           // Não polui models
'write_eloquent_model_mixins' => false,       // Não polui models  
'model_locations' => ['app', 'central-de-laudos']  // Inclui ambos diretórios
```

### Tasks no VS Code

- **IDE Helper: Generate All** - Gera todos os helpers
- **IDE Helper: Models Only** - Apenas models
- **IDE Helper: Facades Only** - Apenas facades

---

**💡 Dica**: Mantenha esta página como referência rápida. Todos os comandos aqui funcionam tanto no VS Code quanto no terminal externo!
