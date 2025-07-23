# Dev Container - To The Future

Este projeto está configurado para usar Dev Containers, proporcionando um ambiente de desenvolvimento consistente e isolado.

## 🚀 Como usar

### Pré-requisitos
- Docker Desktop instalado e rodando
- Visual Studio Code
- Extensão "Dev Containers" do VS Code

### Iniciando o Dev Container

1. Abra o projeto no VS Code
2. VS Code deve detectar a configuração do Dev Container e perguntar se você quer "Reopen in Container"
3. Clique em "Reopen in Container" ou use `Ctrl+Shift+P` e digite "Dev Containers: Reopen in Container"

### O que acontece automaticamente

Quando o Dev Container é criado, o seguinte acontece automaticamente:
- ✅ Serviços Docker são iniciados (PHP, MySQL, Nginx)
- ✅ Dependências PHP (Composer) são instaladas
- ✅ Dependências Node.js (NPM) são instaladas
- ✅ Banco de dados é configurado e migrações executadas
- ✅ Arquivos de ajuda do IDE são gerados
- ✅ Permissões são configuradas corretamente

## 🛠 Extensões incluídas

### Essenciais
- **GitHub Copilot** - Assistente de IA para programação
- **GitHub Copilot Chat** - Chat com IA integrado
- **Intelephense** - IntelliSense avançado para PHP
- **Laravel Pint** - Formatador oficial do Laravel
- **Prettier** - Formatador para JS, Vue, CSS
- **ESLint** - Linter para JavaScript/Vue

### Laravel/Vue específicas
- **Laravel Extra Intellisense** - Autocompletar para Laravel
- **Volar** - Suporte completo para Vue 3
- **Vue TypeScript Plugin** - TypeScript para Vue
- **Tailwind CSS** - Suporte para Tailwind CSS
- **Laravel Blade** - Syntax highlighting para Blade
- **Laravel Artisan** - Comandos Artisan integrados

### Docker
- **Docker** - Gerenciamento completo de containers
- **Remote Containers** - Suporte para Dev Containers

### Qualidade de vida
- **GitLens** - Git integrado avançado
- **Auto Rename Tag** - Renomeia tags HTML/Vue automaticamente
- **Path Intellisense** - Autocompletar para caminhos
- **Material Icon Theme** - Ícones para arquivos

## 🌐 URLs disponíveis

- **Laravel App**: http://localhost:8080
- **Vite Dev Server**: http://localhost:5173
- **MySQL**: localhost:3307

## ⚡ Tarefas disponíveis

Use `Ctrl+Shift+P` e digite "Tasks: Run Task" para acessar:

- **Laravel: Start Dev Server** - Inicia servidor de desenvolvimento Laravel
- **Vite: Start Dev Server** - Inicia servidor Vite para front-end
- **Docker: Start All Services** - Inicia todos os serviços Docker
- **Docker: Stop All Services** - Para todos os serviços Docker
- **Laravel: Run Migrations** - Executa migrações do banco
- **Laravel: Clear Cache** - Limpa cache do Laravel
- **Laravel: Generate IDE Helpers** - Gera arquivos de ajuda do IDE
- **NPM: Install Dependencies** - Instala dependências Node.js
- **Composer: Install Dependencies** - Instala dependências PHP

## 🐳 Comandos Docker úteis

```bash
# Ver logs dos containers
docker-compose logs -f

# Acessar shell do container PHP
docker-compose exec php bash

# Reiniciar serviços
docker-compose restart

# Rebuild containers
docker-compose up --build -d
```

## 🔧 Troubleshooting

### Container não inicia
1. Verifique se o Docker está rodando
2. Verifique se as portas 8080, 5173, 3307 não estão em uso
3. Tente rebuild: `docker-compose up --build -d`

### Problemas de permissão
```bash
# Dentro do container
sudo chown -R www-data:www-data /var/www/html/storage
sudo chown -R www-data:www-data /var/www/html/bootstrap/cache
```

### Dependências não instaladas
```bash
# PHP
composer install

# Node.js
npm install
```

### Banco de dados não conecta
1. Verifique se o MySQL está rodando: `docker-compose ps`
2. Verifique se a porta 3307 está acessível
3. Confira as credenciais no arquivo `.env`

## 📁 Estrutura do Dev Container

```
.devcontainer/
├── devcontainer.json    # Configuração principal
├── settings.json        # Configurações específicas do VS Code
├── tasks.json          # Tarefas automatizadas
├── setup.sh            # Script de inicialização
└── README.md           # Este arquivo
```

## 🔄 Sincronização com o host

Todas as alterações feitas no Dev Container são sincronizadas em tempo real com seu sistema host. Você pode trabalhar normalmente e os arquivos serão mantidos.

## 🎯 Próximos passos

1. Abra o terminal integrado no VS Code
2. Execute `npm run dev` para iniciar o Vite
3. Acesse http://localhost:8080 para ver sua aplicação
4. Comece a programar com o poder do Copilot! 🚀
