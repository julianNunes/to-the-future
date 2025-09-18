# 💻 Ambiente de Desenvolvimento - To The Future

## 🎯 Filosofia do Ambiente

### Desenvolvimento Híbrido

O ambiente combina o melhor de dois mundos:

- **Docker**: Para serviços (PHP, MySQL, Nginx)  
- **Host Local**: Para ferramentas de desenvolvimento (VS Code, Git)
- **Scripts Bridge**: Conectam ferramentas locais aos containers

### Vantagens

- ✅ **Performance nativa** - VS Code roda no host
- ✅ **Isolamento garantido** - Serviços em containers
- ✅ **Flexibilidade total** - Use ferramentas locais quando quiser
- ✅ **Compatibilidade universal** - Funciona em qualquer Linux
- ✅ **Zero configuração** - Tudo automático após setup

## 🚀 Configuração Inicial

### Pré-requisitos

```bash
# Verificar instalações necessárias
docker --version          # >= 20.10
docker compose version    # >= 2.0
code --version            # VS Code
git --version             # Git
```

### Setup Automático

```bash
# Um comando faz tudo
./start.sh setup

# O que acontece:
# 1. Verifica dependências
# 2. Constrói containers Docker
# 3. Instala extensões VS Code
# 4. Configura workspace
# 5. Inicia ambiente
# 6. Roda healthchecks
```

### Setup Manual (se preferir)

```bash
# 1. Construir containers
docker compose build

# 2. Instalar extensões VS Code  
./start.sh install-extensions

# 3. Abrir workspace
code to-the-future.code-workspace

# 4. Iniciar containers
./start.sh dev
```

## 🎨 Configuração VS Code

### Workspace Pré-configurado

**Arquivo**: `to-the-future.code-workspace`

#### Configurações Automáticas

```json
{
  "settings": {
    // PHP executado via container
    "php.executablePath": "${workspaceFolder}/scripts/php.sh",
    "php.validate.executablePath": "${workspaceFolder}/scripts/php.sh",
    
    // Composer via container
    "composer.executablePath": "${workspaceFolder}/scripts/composer.sh",
    
    // PHP CS Fixer via container
    "php-cs-fixer.executablePath": "${workspaceFolder}/scripts/php-cs-fixer.sh",
    "php-cs-fixer.onsave": true,
    
    // IntelliSense configurado
    "intelephense.environment.phpVersion": "7.4",
    "intelephense.files.maxSize": 5000000,
    
    // Formatação automática
    "editor.formatOnSave": true,
    "[php]": {
      "editor.defaultFormatter": "junstyle.php-cs-fixer"
    }
  }
}
```

### Extensões Instaladas Automaticamente

```json
{
  "recommendations": [
    "bmewburn.vscode-intelephense-client",  // PHP Language Server
    "junstyle.php-cs-fixer",               // Formatação PHP
    "onecentlin.laravel-blade",            // Laravel Blade
    "ryannaddy.laravel-artisan",           // Laravel Artisan
    "ms-vscode.vscode-docker",             // Docker
    "eamodio.gitlens",                     // Git enhanced
    "github.copilot",                      // AI Assistant
    "ms-vscode.vscode-json"                // JSON support
  ]
}
```

### Tasks Integradas

Pressione `Ctrl+Shift+P` → "Tasks: Run Task"

#### Tarefas Disponíveis

- **Laravel: Start Development** - `./start.sh dev`
- **Laravel: Stop Development** - `./start.sh stop`
- **Laravel: Artisan Command** - Input customizado
- **Laravel: Migration Run** - `./scripts/artisan.sh migrate`
- **Composer: Install** - `./scripts/composer.sh install`
- **Composer: Update** - `./scripts/composer.sh update`
- **NPM: Install** - `./scripts/npm.sh install`
- **NPM: Development Build** - `./scripts/npm.sh run dev`
- **NPM: Watch Mode** - `./scripts/npm.sh run watch`

#### Configuração das Tasks

```json
{
  "tasks": [
    {
      "label": "Laravel: Start Development",
      "type": "shell",
      "command": "./start.sh dev",
      "group": "build",
      "presentation": {
        "echo": true,
        "reveal": "always",
        "focus": false,
        "panel": "shared"
      }
    }
  ]
}
```

## 🔧 Scripts de Integração

### Localização: `./scripts/`

#### 1. **php.sh** - Executor PHP

```bash
# Exemplos de uso
./scripts/php.sh -v                    # Versão PHP
./scripts/php.sh -m                    # Módulos carregados
./scripts/php.sh artisan migrate       # Via PHP direto
./scripts/php.sh vendor/bin/phpunit    # Executar testes
```

#### 2. **composer.sh** - Gerenciador de Dependências

```bash
# Gerenciamento de pacotes
./scripts/composer.sh install          # Instalar dependências
./scripts/composer.sh require monolog/monolog  # Adicionar pacote
./scripts/composer.sh update           # Atualizar pacotes
./scripts/composer.sh dump-autoload    # Recriar autoload
./scripts/composer.sh show             # Listar pacotes
```

#### 3. **artisan.sh** - Comandos Laravel

```bash
# Comandos essenciais
./scripts/artisan.sh migrate           # Executar migrations
./scripts/artisan.sh migrate:rollback  # Reverter migrations
./scripts/artisan.sh make:model User   # Criar model
./scripts/artisan.sh make:controller UserController  # Criar controller
./scripts/artisan.sh queue:work        # Processar filas
./scripts/artisan.sh tinker            # REPL Laravel
./scripts/artisan.sh cache:clear       # Limpar cache
./scripts/artisan.sh config:clear      # Limpar config cache
./scripts/artisan.sh route:list        # Listar rotas
```

#### 4. **npm.sh** - Gerenciador JavaScript

```bash
# Gerenciamento frontend
./scripts/npm.sh install               # Instalar dependências
./scripts/npm.sh run dev               # Build desenvolvimento
./scripts/npm.sh run production        # Build produção
./scripts/npm.sh run watch             # Watch mode (auto-rebuild)
./scripts/npm.sh run hot               # Hot reload
./scripts/npm.sh audit                 # Verificar vulnerabilidades
```

#### 5. **php-cs-fixer.sh** - Formatação de Código

```bash
# Formatação automática
./scripts/php-cs-fixer.sh fix          # Formatar todo o código
./scripts/php-cs-fixer.sh fix --dry-run # Apenas verificar
./scripts/php-cs-fixer.sh fix app/     # Formatar pasta específica
```

### Como os Scripts Funcionam

```bash
#!/bin/bash
# Exemplo: scripts/composer.sh

# 1. Verificar se ambiente está rodando
if ! docker compose ps | grep -q "to-the-future-php.*Up"; then
    echo "❌ Container não está rodando!"
    echo "Execute: ./start.sh dev"
    exit 1
fi

# 2. Executar comando no container apropriado
docker compose exec php composer "$@"

# 3. Retornar código de saída do container
exit $?
```

## 🔄 Fluxo de Desenvolvimento

### 1. Início do Dia

```bash
# Iniciar ambiente (se não estiver rodando)
./start.sh dev

# Abrir VS Code no workspace configurado
code to-the-future.code-workspace

# Verificar se tudo está OK
./start.sh status
```

### 2. Desenvolvimento Típico

```bash
# Trabalhar com código - usar VS Code normalmente
# IntelliSense, debugging, formatação funcionam automaticamente

# Executar migrations
./scripts/artisan.sh migrate

# Instalar nova dependência
./scripts/composer.sh require intervention/image

# Build assets frontend
./scripts/npm.sh run dev

# Executar testes
./scripts/php.sh vendor/bin/phpunit

# Ver logs em tempo real (terminal separado)
./start.sh logs
```

### 3. Debugging

```bash
# Entrar no container para investigação
docker compose exec app bash

# Ver logs específicos
docker compose logs app
docker compose logs mysql
docker compose logs nginx

# Executar comandos de debug
./scripts/artisan.sh tinker
```

### 4. Final do Dia

```bash
# Opção 1: Manter rodando (recomendado)
# Os containers continuam rodando em background

# Opção 2: Parar containers (preserva dados)
./start.sh stop

# Opção 3: Limpeza completa (remove tudo)
./start.sh clean
```

## 🛠️ Configurações Avançadas

### Xdebug (Debugging PHP)

**Já configurado automaticamente!**

#### Configuração VS Code

```json
{
  "launch": {
    "version": "0.2.0",
    "configurations": [
      {
        "name": "Listen for Xdebug",
        "type": "php",
        "request": "launch",
        "port": 9003,
        "pathMappings": {
          "/var/www": "${workspaceFolder}"
        }
      }
    ]
  }
}
```

#### Como Usar

1. Colocar breakpoints no VS Code
2. Pressionar `F5` (Start Debugging)
3. Acessar a página no navegador
4. Debug para automaticamente nos breakpoints

### PHP CS Fixer (Formatação)

**Configuração**: `.php-cs-fixer.php`

```php
<?php
return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->exclude('vendor')
    );
```

### Laravel Mix (Frontend Build)

**Configuração**: `webpack.mix.js`

```javascript
const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .options({
       processCssUrls: false
   });

// Hot reloading
if (mix.inProduction()) {
    mix.version();
}
```

## 📊 Monitoramento do Ambiente

### Verificação de Status

```bash
# Status completo
./start.sh status

# Recursos utilizados
docker stats --no-stream

# Logs em tempo real
./start.sh logs

# Verificar saúde dos containers
docker compose ps
```

### Métricas de Performance

```bash
# Uso de recursos
docker system df

# Containers ativos
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

# Volumes utilizados
docker volume ls
```

### Limpeza e Manutenção

```bash
# Limpeza leve (mantém volumes)
docker compose down

# Limpeza moderada (remove containers, mantém volumes)
./start.sh clean

# Limpeza pesada (remove tudo, incluindo volumes)
docker compose down -v
docker system prune -af
```

## 🔧 Customização

### Adicionando Novos Scripts

```bash
# Criar novo script em scripts/
touch scripts/meu-comando.sh
chmod +x scripts/meu-comando.sh
```

```bash
#!/bin/bash
# scripts/meu-comando.sh

if ! docker compose ps | grep -q "novacentral-app.*Up"; then
    echo "❌ Container não está rodando"
    exit 1
fi

# Seu comando personalizado
docker compose exec app php artisan meu:comando "$@"
```

### Modificando Configurações Docker

1. Editar `docker-compose.yml`
2. Rebuild se necessário: `docker compose up --build`
3. Testar alterações

### Adicionando Extensões VS Code

1. Editar `to-the-future.code-workspace`
2. Adicionar na seção `extensions.recommendations`
3. Executar `./start.sh install-extensions`

## 🎯 Melhores Práticas

### Desenvolvimento

1. **Sempre use o workspace**: `to-the-future.code-workspace`
2. **Use scripts em vez de comandos diretos**: `./scripts/artisan.sh` vs `php artisan`
3. **Mantenha containers rodando**: Evite `start/stop` constante
4. **Use tasks do VS Code**: Automação via `Ctrl+Shift+P`
5. **Comite apenas código**: `.gitignore` já configurado

### Performance

1. **Volumes para cache**: vendor/ e node_modules/ são cacheados
2. **Não edite dentro de containers**: Use bind mounts
3. **Use healthchecks**: Verificação automática de containers
4. **Monitore recursos**: `docker stats` periodicamente

### Segurança

1. **Não exponha senhas**: Use `.env` para configurações
2. **Rede isolada**: Containers em rede privada
3. **Usuário correto**: Processos executam como www-data
4. **Volumes seguros**: Dados sensíveis em volumes nomeados

---

**🎉 Ambiente configurado para máxima produtividade e mínimo atrito!**
