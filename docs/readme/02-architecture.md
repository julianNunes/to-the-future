# 🏗️ Arquitetura do Projeto - To The Future

## 📋 Visão Geral

O **To The Future** é um sistema web moderno desenvolvido com uma arquitetura SPA (Single Page Application):

- **Framework**: Laravel 10.x (PHP 8.2)
- **Banco de Dados**: MySQL 8.0
- **Frontend**: Vue.js 3 + Inertia.js + Vuetify 3
- **Build Tool**: Vite
- **Ambiente**: Docker com VS Code integrado
- **Filosofia**: Desenvolvimento no host com executáveis no Docker

## 🐳 Arquitetura Docker

### Containers de Produção

#### 1. **to-the-future-php** (PHP-FPM)

- **Base**: PHP 8.2 Alpine Linux
- **Função**: Processar código PHP/Laravel
- **Porta**: 9000 (interno), 9003 (Xdebug)
- **Extensões**: GD, PDO MySQL, Zip, Imagick, Xdebug
- **Ferramentas**: Composer, Node.js, NPM

#### 2. **to-the-future-nginx** (Servidor Web)

- **Base**: Nginx Alpine
- **Função**: Servidor web e proxy reverso
- **Porta**: 8080 → 80 (container)
- **Configuração**: Otimizada para Laravel

#### 3. **to-the-future-db** (Banco de Dados)

- **Base**: MySQL 8.0 customizada
- **Função**: Banco de dados principal
- **Porta**: 3306 → 3306 (container)
- **Configuração**: Otimizada para desenvolvimento

## 🌐 Rede e Comunicação

### Ambiente de Desenvolvimento

O projeto utiliza **Docker Compose** com uma rede customizada:

```yaml
# Rede: to-the-future_default
- php: to-the-future-php-1
- nginx: to-the-future-nginx-1  
- db: to-the-future-db-1
```

### Portas e Endpoints

```
http://localhost:8080           # Aplicação web
http://localhost:3306           # Banco MySQL (dev)
9003                            # Xdebug (remoto)
5173                            # Vite dev server (quando ativo)
```

### Rede Docker

```yaml
novacentral:
  driver: bridge
  containers:
    - app (novacentral-app)
    - nginx (novacentral-nginx) 
    - mysql (novacentral-mysql)
    - supervisor (novacentral-supervisor)
```

### Comunicação Interna

- **nginx → app**: Via hostname `app:9000` (PHP-FPM)
- **app → mysql**: Via hostname `mysql:3306`
- **supervisor → mysql**: Via hostname `mysql:3306`

### Exposição para Host

- **HTTP**: `localhost:8000` → nginx:80
- **MySQL**: `localhost:3306` → mysql:3306
- **Xdebug**: `localhost:9003` → app:9003

## 💾 Volumes e Persistência

### Volumes Nomeados

```yaml
mysql-data:           # Dados MySQL (persistente)
app-vendor:           # Cache Composer (performance)
app-node-modules:     # Cache NPM (performance)
```

### Bind Mounts

```yaml
./:/var/www                              # Código fonte
./docker/nginx/:/etc/nginx/conf.d/       # Config Nginx
./docker/php/:/usr/local/etc/php/conf.d/ # Config PHP
```

## 🔧 Integração Host ↔ Docker

### Filosofia Híbrida

**Por que não Dev Container?**

- **Performance**: Execução direta no host é mais rápida
- **Flexibilidade**: Use ferramentas locais quando preferir
- **Compatibilidade**: Funciona em qualquer distro Linux
- **Isolamento**: Docker apenas para serviços essenciais

### Scripts Bridge

Localização: `./scripts/`

#### Executores de Comando

- **`php.sh`**: Executa PHP via container
- **`composer.sh`**: Gerencia dependências PHP
- **`artisan.sh`**: Comandos Laravel
- **`npm.sh`**: Gerencia dependências JavaScript

#### Como Funcionam

```bash
#!/bin/bash
# Exemplo: scripts/php.sh

# 1. Verificar se containers estão rodando
if ! docker compose ps | grep -q "to-the-future-php.*Up"; then
    echo "❌ Container não está rodando. Execute: ./start.sh dev"
    exit 1
fi

# 2. Executar comando no container
docker compose exec php php "$@"
```

### VS Code Integration

#### Workspace Configurado

- **Arquivo**: `to-the-future.code-workspace`
- **PHP Path**: `${workspaceFolder}/scripts/php.sh`
- **Composer Path**: `${workspaceFolder}/scripts/composer.sh`
- **CS Fixer**: `${workspaceFolder}/scripts/php-cs-fixer.sh`

#### Extensões Automáticas

- **Intelephense**: PHP Language Server
- **PHP CS Fixer**: Formatação automática
- **Laravel Blade**: Syntax highlighting
- **GitLens**: Git integration
- **Docker**: Container management

## 🛠️ Ferramentas e Tecnologias

### Backend (Laravel)

```
Laravel 8.x
├── PHP 7.4
├── MySQL 8.0
├── Composer 2.x
├── Artisan CLI
└── Extensões PHP:
    ├── GD (manipulação de imagens)
    ├── Imagick (processamento avançado)
    ├── PDO MySQL (banco de dados)
    ├── Zip (compressão)
    └── Xdebug 3.1.6 (debugging)
```

### Frontend

```
Blade Templates
├── jQuery 3.x
├── Bootstrap 4.x
├── JavaScript ES6+
├── SCSS/CSS3
└── NPM/Webpack (Laravel Mix)
```

### DevOps

```
Docker
├── Docker Compose 2.x
├── Multi-stage builds
├── Alpine Linux (performance)
├── Health checks
└── Volume optimization

VS Code
├── Workspace configurado
├── IntelliSense para PHP
├── Tasks integradas
├── Debugging configurado
└── Extensões automáticas
```

## 📊 Performance e Recursos

### Recursos Mínimos

- **RAM**: 4GB (8GB recomendado)
- **CPU**: 2 cores (4 cores recomendado)
- **Disco**: 10GB livres
- **Docker**: 4GB RAM dedicado

### Recursos por Container

```
novacentral-app:      ~512MB RAM
novacentral-nginx:    ~50MB RAM
novacentral-mysql:    1-4GB RAM (configurável)
novacentral-supervisor: ~256MB RAM
```

### Otimizações Implementadas

- **Volume caching**: vendor/ e node_modules/ em volumes
- **Multi-stage builds**: Imagens Docker otimizadas
- **Health checks**: Monitoramento automático
- **MySQL tuning**: Buffer pool e query cache otimizados

## 🔄 Fluxo de Dados

### Desenvolvimento

```
Desenvolvedor
    ↓ (edita código)
VS Code (Host)
    ↓ (bind mount)
Container App
    ↓ (processa PHP)
Container Nginx
    ↓ (serve HTTP)
Navegador (localhost:8000)
```

### Banco de Dados

```
Aplicação Laravel
    ↓ (Eloquent ORM)
MySQL Container
    ↓ (persistência)
Volume mysql-data
```

### Filas/Jobs

```
Laravel App
    ↓ (dispatch job)
Queue (MySQL tables)
    ↓ (supervisor monitora)
Supervisor Container
    ↓ (executa worker)
Job Processado
```

## 🛡️ Segurança

### Isolamento

- **Rede privada**: Containers em rede isolada
- **Volumes seguros**: Dados em volumes Docker
- **Usuários**: Processos com usuário www-data
- **Portas**: Apenas essenciais expostas

### Configurações de Segurança

```yaml
# docker-compose.yml
services:
  app:
    user: www-data
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
  mysql:
    environment:
      - MYSQL_ROOT_PASSWORD=${DB_PASSWORD}
    # Apenas interno: não exposto
```

---

**Esta arquitetura garante:**

- ✅ **Isolamento** completo do ambiente
- ✅ **Performance** otimizada para desenvolvimento  
- ✅ **Compatibilidade** com ferramentas modernas
- ✅ **Escalabilidade** para produção
- ✅ **Manutenibilidade** através de padrões
