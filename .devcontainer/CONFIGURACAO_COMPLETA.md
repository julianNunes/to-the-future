# Configuração Completa do Dev Container - To The Future

## 📋 Visão Geral

Este projeto Laravel 10 + InertiaJS + Vue.js 3 + Vuetify 3 está configurado com um ambiente Dev Container completo, oferecendo:

- ✅ Ambiente de desenvolvimento isolado e reproduzível
- ✅ Suporte completo ao GitHub Copilot
- ✅ Integração com Docker para gerenciamento de containers
- ✅ Configuração automática de dependências e banco de dados
- ✅ Hot-reload para desenvolvimento front-end e back-end

---

## 🏗️ Arquitetura do Projeto

### **Dockerfiles Configurados**

#### **1. `Dockerfile` (Produção)**
- **Multi-stage build** para otimização
- **Stage 1 (Builder)**: Compila dependências PHP e Node.js, builds assets
- **Stage 2 (Final)**: Imagem runtime otimizada com Apache
- **Extensões PHP**: PDO MySQL, GD, MBString, ZIP
- **Node.js 18** para builds de produção
- **Apache** configurado com mod_rewrite para Laravel

#### **2. `Dockerfile.dev` (Desenvolvimento)**
- **PHP-FPM 8.2** para desenvolvimento
- **Docker-in-Docker** - permite uso de comandos Docker dentro do container
- **Node.js + NPM** para ferramentas de front-end
- **Usuário www-data** configurado com sudo sem senha
- **Permissões sincronizadas** com o host via PUID/PGID
- **Extensions PHP**: PDO MySQL, GD, MBString, ZIP

#### **3. `Dockerfile.vite` (Front-end)**
- **Node.js 20 Alpine** - imagem leve e rápida
- **Cache otimizado** para dependências NPM
- **Hot-reload** configurado para desenvolvimento

---

## 🐳 Docker Compose

### **Serviços Configurados:**

#### **PHP (Desenvolvimento)**
- Build customizado com `Dockerfile.dev`
- **Network mode: host** para simplicidade de conexões
- Volumes sincronizados com cache otimizado
- Healthcheck na porta 9000 (PHP-FPM)

#### **Nginx**
- **Porta 8080** (evita conflitos com outros serviços)
- Proxy para PHP-FPM via localhost:9000
- DocumentRoot apontando para `/public`

#### **Vite (Hot-reload)**
- **Porta 5173** para desenvolvimento
- Chokidar polling otimizado
- Volume node_modules compartilhado

#### **MySQL 8.0**
- **Porta 3307** (evita conflitos com MySQL local)
- Healthcheck configurado
- Credenciais: admin/root1234
- Database: laravel

---

## 🚀 Dev Container - Configurações Detalhadas

### **Arquivo Principal: `.devcontainer/devcontainer.json`**

#### **Extensões VS Code Instaladas Automaticamente:**

##### **🤖 IA & Produtividade**
- `GitHub.copilot` - Assistente de IA para código
- `GitHub.copilot-chat` - Chat integrado com IA

##### **🐘 PHP & Laravel**
- `bmewburn.vscode-intelephense-client` - IntelliSense avançado PHP
- `open-southeners.laravel-pint` - Formatador oficial Laravel
- `amiralizadeh948.laravel-extra-intellisense` - Autocompletar Laravel
- `codingyu.laravel-goto-view` - Navegação controller → view
- `ryannaddy.laravel-artisan` - Comandos Artisan integrados
- `xdebug.php-debug` - Debug PHP com XDebug

##### **🎨 Vue.js & Frontend**
- `Vue.volar` - Suporte oficial Vue 3 (Language Server)
- `Vue.vscode-typescript-vue-plugin` - TypeScript para Vue
- `esbenp.prettier-vscode` - Formatador JS/Vue/CSS
- `dbaeumer.vscode-eslint` - Linter JavaScript/Vue
- `formulahendry.auto-rename-tag` - Renomeia tags automaticamente

##### **🐳 Docker & DevOps**
- `ms-azuretools.vscode-docker` - Gerenciamento Docker completo
- `ms-vscode-remote.remote-containers` - Suporte Dev Containers

##### **🛠️ Ferramentas Gerais**
- `eamodio.gitlens` - Git avançado integrado
- `christian-kohler.path-intellisense` - Autocompletar caminhos
- `ms-vscode.vscode-json` - Suporte JSON aprimorado
- `redhat.vscode-yaml` - Suporte YAML

#### **Funcionalidades Docker-in-Docker:**
```jsonc
"features": {
    "ghcr.io/devcontainers/features/docker-outside-of-docker:1": {
        "version": "latest",
        "enableNonRootDocker": "true"
    }
}
```

#### **Portas Encaminhadas:**
- **8080**: Laravel App (HTTP)
- **5173**: Vite Dev Server (HTTP)
- **3307**: MySQL Database

#### **Montagens Docker:**
```jsonc
"mounts": [
    "source=/var/run/docker.sock,target=/var/run/docker.sock,type=bind"
]
```

---

## ⚙️ Configurações VS Code

### **Arquivo: `.devcontainer/settings.json`**

#### **Docker Integration:**
```jsonc
"docker.host": "unix:///var/run/docker.sock",
"docker.dockerPath": "docker",
"docker.machineNaming": "both"
```

#### **PHP/Laravel:**
```jsonc
"php.suggest.basic": false,
"php.validate.executablePath": "/usr/local/bin/php",
"intelephense.files.maxSize": 5000000,
"intelephense.environment.phpVersion": "8.2.0"
```

#### **Vue.js:**
```jsonc
"emmet.includeLanguages": {
    "vue": "html",
    "vue-html": "html"
},
"vetur.validation.template": false,
"vetur.validation.script": false,
"vetur.validation.style": false
```

#### **GitHub Copilot:**
```jsonc
"github.copilot.enable": {
    "*": true,
    "php": true,
    "javascript": true,
    "vue": true
}
```

---

## 🔄 Automação & Scripts

### **Script de Setup: `.devcontainer/setup.sh`**

#### **Funcionalidades Automáticas:**
1. **Aguarda serviços** (MySQL na porta 3307)
2. **Configura permissões** (storage, bootstrap/cache, node_modules)
3. **Instala dependências** (Composer + NPM)
4. **Configura Laravel** (.env, key:generate, migrações)
5. **Limpa caches** (config, cache, view, route)
6. **Gera IDE helpers** (autocomplete melhorado)

### **Tarefas VS Code: `.devcontainer/tasks.json`**

#### **Tarefas Disponíveis:**
- `Laravel: Start Dev Server` - php artisan serve
- `Vite: Start Dev Server` - npm run dev
- `Docker: Start All Services` - docker-compose up -d
- `Docker: Stop All Services` - docker-compose down
- `Laravel: Run Migrations` - php artisan migrate
- `Laravel: Clear Cache` - php artisan cache:clear
- `Laravel: Generate IDE Helpers` - php artisan ide-helper:generate
- `NPM: Install Dependencies` - npm install
- `Composer: Install Dependencies` - composer install

---

## 📦 Dependências & Stack

### **Backend:**
- **PHP 8.2** com FPM
- **Laravel 10** framework
- **MySQL 8.0** database
- **Composer** para gerenciamento de dependências

### **Frontend:**
- **Vue.js 3** com Composition API
- **InertiaJS** para SPA sem API
- **Vuetify 3** como framework de componentes UI
- **Vite** para build e hot-reload
- **NPM** para gerenciamento de dependências

### **DevOps:**
- **Docker** & **Docker Compose**
- **Nginx** como proxy reverso
- **Dev Containers** para ambiente isolado

---

## 🚦 Como Usar

### **1. Primeira Configuração:**
```bash
# 1. Instale a extensão "Dev Containers" no VS Code
# 2. Abra o projeto no VS Code
# 3. Clique em "Reopen in Container" quando solicitado
# 4. Aguarde a configuração automática (3-5 minutos na primeira vez)
```

### **2. Desenvolvimento Diário:**
```bash
# Terminal 1: Inicie os serviços Docker
docker-compose up -d

# Terminal 2: Inicie o Vite para hot-reload
npm run dev

# Acesse: http://localhost:8080
```

### **3. Comandos Úteis:**
```bash
# Ver logs dos containers
docker-compose logs -f

# Acessar shell do PHP
docker-compose exec php bash

# Executar migrations
php artisan migrate

# Limpar caches
php artisan optimize:clear

# Gerar IDE helpers
php artisan ide-helper:generate
```

---

## 🔧 Troubleshooting

### **Container não inicia:**
```bash
# Verifique se Docker está rodando
docker --version

# Rebuild containers
docker-compose up --build -d

# Verifique portas em uso
netstat -tulpn | grep -E '(8080|5173|3307)'
```

### **Problemas de permissão:**
```bash
# Dentro do container
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### **Dependências não instaladas:**
```bash
# Reinstalar dependências
composer install
npm install

# Limpar caches
php artisan optimize:clear
npm run build
```

---

## 📁 Estrutura de Arquivos

```
.devcontainer/
├── devcontainer.json    # Configuração principal do Dev Container
├── settings.json        # Configurações específicas do VS Code
├── tasks.json          # Tarefas automatizadas
├── setup.sh            # Script de inicialização automática
└── README.md           # Documentação completa (este arquivo)

docker/
├── Dockerfile          # Imagem de produção (multi-stage)
├── Dockerfile.dev      # Imagem de desenvolvimento (PHP-FPM)
├── Dockerfile.vite     # Imagem para Vite/Node.js
├── docker-compose.yml  # Orquestração dos serviços
├── entrypoint.sh       # Script de entrada produção
└── entrypoint-dev.sh   # Script de entrada desenvolvimento

nginx/
└── default.conf        # Configuração Nginx para Laravel
```

---

## ✨ Benefícios da Configuração

### **🎯 Para Desenvolvimento:**
- **Ambiente isolado** - Não interfere com configurações locais
- **Reproduzível** - Mesmo ambiente para toda equipe
- **Hot-reload** - Mudanças refletidas instantaneamente
- **Debug integrado** - XDebug configurado
- **IDE helpers** - Autocompletar melhorado para Laravel

### **🚀 Para Produção:**
- **Multi-stage build** - Imagens otimizadas
- **Apache configurado** - Ready-to-deploy
- **Assets compilados** - Build automático do frontend
- **Segurança** - Usuários e permissões configurados

### **🤖 Para Produtividade:**
- **GitHub Copilot** - IA para acelerar desenvolvimento
- **Tarefas automatizadas** - Um clique para ações comuns
- **Git integrado** - GitLens para melhor workflow
- **Docker gerenciado** - Containers via VS Code

---

## 🎉 Resultado Final

Você agora tem um ambiente de desenvolvimento:

✅ **Completamente funcional** para Laravel + InertiaJS + Vue.js + Vuetify
✅ **Isolado e reproduzível** via Dev Containers
✅ **Com GitHub Copilot integrado** para acelerar desenvolvimento
✅ **Docker-in-Docker habilitado** para gerenciar containers
✅ **Hot-reload automático** para frontend e backend
✅ **Pronto para produção** com builds otimizados
✅ **Configuração zero** - tudo automatizado via scripts

**🚀 Basta abrir no VS Code e começar a programar!**
