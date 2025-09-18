# 🚀 Migração: Dev Container → Host-based Development

## 📋 Resumo da Migração

Este documento descreve a migração completa do projeto **To The Future** do ambiente Dev Container para um ambiente de desenvolvimento host-based, baseado nas melhores práticas do projeto **Nova Central V4.1**.

## ✅ O que foi Migrado

### 1. **Extensões do VS Code**

- **Antes**: Extensões instaladas automaticamente no Dev Container
- **Depois**: Script de instalação local `install-vscode-extensions.sh`
- **Benefícios**:
  - Extensões disponíveis no host
  - Melhor performance
  - Configuração reutilizável

### 2. **Workspace Configuration**

- **Arquivo**: `to-the-future.code-workspace`
- **Migradas**: Todas as configurações do `devcontainer.json` e `settings.json`
- **Novas funcionalidades**:
  - Tasks integradas para Laravel/Artisan
  - Configurações de debug (Xdebug)
  - Formatação automática para PHP, Vue, JS, TS
  - Integração com PHP CS Fixer
  - Configuração específica para Vue 3 + Inertia

### 3. **Scripts de Integração**

- **Adaptados**: Todos os scripts em `scripts/` para usar o serviço `php` do docker-compose
- **Scripts atualizados**:
  - `artisan.sh` - Comandos Laravel
  - `composer.sh` - Gerenciamento de dependências
  - `npm.sh` - Build do frontend
  - `php.sh` - Execução de PHP
  - `php-cs-fixer.sh` - Formatação de código
  - `php-wrapper.sh` - Wrapper para Intelephense

### 4. **Sistema de Gerenciamento**

- **Novo arquivo**: `start.sh` adaptado do Nova Central
- **Funcionalidades**:
  - Ambiente de desenvolvimento (`dev`)
  - Ambiente completo com Vite (`dev-full`)
  - Setup automático para novos desenvolvedores
  - Geração de IDE Helpers
  - Instalação de extensões VS Code
  - Verificação de ambiente

## 🎯 Configuração do Novo Ambiente

### Pré-requisitos

```bash
# Verificar instalações necessárias
docker --version          # >= 20.10
docker compose version    # >= 2.0
code --version            # VS Code
git --version             # Git
```

### Setup Inicial (Uma única vez)

```bash
# Configuração completa automatizada
./start.sh setup
```

Este comando faz tudo automaticamente:

1. ✅ Verifica pré-requisitos (Docker, VS Code)
2. ✅ Configura permissões dos scripts
3. ✅ Instala extensões do VS Code
4. ✅ Cria arquivo .env (se necessário)
5. ✅ Inicia o ambiente de desenvolvimento
6. ✅ Mostra próximos passos

### Desenvolvimento Diário

```bash
# Iniciar ambiente básico
./start.sh dev

# OU iniciar com Vite (hot reload frontend)
./start.sh dev-full

# Abrir VS Code no workspace configurado
code to-the-future.code-workspace
```

## 🔧 Principais Funcionalidades

### Comandos Disponíveis

```bash
# Desenvolvimento
./start.sh dev           # Ambiente básico (PHP, Nginx, MySQL)
./start.sh dev-full      # Ambiente + Vite hot reload
./start.sh stop          # Parar containers
./start.sh logs          # Ver logs em tempo real
./start.sh status        # Status dos containers

# Utilitários
./start.sh setup         # Setup inicial completo
./start.sh clean         # Limpeza completa
./start.sh check         # Verificar ambiente

# IDE & IntelliSense
./start.sh ide-helper    # Gerar todos os helpers
./start.sh ide-models    # Gerar helper dos models
./start.sh ide-facades   # Gerar helper das facades
```

### Scripts de Desenvolvimento

```bash
# Laravel/Artisan
./scripts/artisan.sh migrate
./scripts/artisan.sh make:model User
./scripts/artisan.sh tinker

# Composer
./scripts/composer.sh install
./scripts/composer.sh require intervention/image

# NPM/Frontend
./scripts/npm.sh install
./scripts/npm.sh run dev
./scripts/npm.sh run build

# PHP CS Fixer
./scripts/php-cs-fixer.sh fix
./scripts/php-cs-fixer.sh fix app/
```

### Tasks do VS Code

Pressione `Ctrl+Shift+P` → "Tasks: Run Task":

- **Laravel: Start Development** - Inicia ambiente
- **Laravel: Stop Development** - Para ambiente
- **Artisan: Migration Run** - Executa migrations
- **Vite: Development Build** - Build do frontend
- **Generate IDE Helpers** - Gera helpers para IntelliSense
- **PHP CS Fixer: Fix All** - Formata todo o código

### Debug PHP (Xdebug)

1. Coloque breakpoints no VS Code
2. Pressione `F5` (Listen for Xdebug)
3. Acesse a página no navegador
4. Debug para automaticamente

## 🔄 Comparação: Antes vs Depois

| Aspecto | Dev Container | Host-based |
|---------|---------------|------------|
| **Performance VS Code** | Lenta (remoto) | Nativa (local) |
| **Extensões** | Container only | Host disponível |
| **Git** | Container | Host nativo |
| **Startup Time** | 2-5 minutos | 30-60 segundos |
| **IntelliSense** | Limitado | Completo |
| **Debugging** | Complexo | Simples |
| **Recursos** | Alto uso memória | Otimizado |

## 🚀 Vantagens do Novo Ambiente

### Performance

- ✅ VS Code roda nativamente no host
- ✅ Git operations mais rápidas
- ✅ IntelliSense sem latência
- ✅ File watching mais eficiente

### Desenvolvimento

- ✅ Debugging PHP integrado (Xdebug)
- ✅ Tasks predefinidas no VS Code
- ✅ Formatação automática (PHP CS Fixer + Prettier)
- ✅ Hot reload para Vue/JS (Vite)
- ✅ IDE Helpers para melhor autocomplete

### Flexibilidade

- ✅ Uso de ferramentas locais quando necessário
- ✅ Ambiente híbrido (containers + host)
- ✅ Scripts bridge para integração perfeita
- ✅ Setup automatizado para novos desenvolvedores

## 📱 Acessos e Portas

| Serviço | URL/Porta | Descrição |
|---------|-----------|-----------|
| **Laravel App** | <http://localhost:8080> | Aplicação principal |
| **Vite Dev Server** | <http://localhost:5173> | Hot reload frontend |
| **MySQL** | localhost:3307 | Banco de dados |
| **Nginx** | Container interno | Servidor web |
| **PHP-FPM** | Container interno | Processos PHP |

### Credenciais MySQL

- **Host**: localhost
- **Porta**: 3307  
- **Database**: laravel
- **Usuário**: admin
- **Senha**: root1234

## 🛠️ Configurações Específicas

### Intelephense (PHP IntelliSense)

```json
{
  "php.executablePath": "${workspaceFolder}/scripts/php-wrapper.sh",
  "intelephense.environment.phpVersion": "8.2.0",
  "intelephense.environment.includePaths": [
    "${workspaceFolder}/vendor",
    "${workspaceFolder}/_ide_helper.php"
  ]
}
```

### Vue 3 + Inertia

```json
{
  "vue.server.hybridMode": true,
  "vue.complete.casing.tags": "kebab",
  "vue.complete.casing.props": "camel",
  "[vue]": {
    "editor.defaultFormatter": "esbenp.prettier-vscode"
  }
}
```

### Formatação Automática

- **PHP**: Intelephense (PSR-12) + PHP CS Fixer
- **Vue/JS/TS**: Prettier
- **Save**: Formatação automática ativada
- **Paste**: Formatação automática ativada

## 🚨 Troubleshooting

### Container não inicia

```bash
# Verificar status
./start.sh status

# Ver logs
./start.sh logs

# Limpeza completa
./start.sh clean
./start.sh dev
```

### Intelephense não funciona

```bash
# Testar wrapper PHP
./start.sh test-php-wrapper

# Gerar IDE helpers
./start.sh ide-helper

# Reiniciar VS Code
```

### Problemas de permissão

```bash
# Corrigir permissões Laravel
./start.sh fix-permissions
```

### Extensões não instaladas

```bash
# Reinstalar extensões
./start.sh install-extensions
```

## 📚 Próximos Passos

1. **Teste o novo ambiente**:

   ```bash
   ./start.sh setup
   code to-the-future.code-workspace
   ```

2. **Desenvolva normalmente**:
   - Use tasks do VS Code (Ctrl+Shift+P)
   - Debug com F5
   - Formatação automática no save

3. **Compartilhe com a equipe**:
   - Novo setup é mais simples
   - Um comando faz tudo: `./start.sh setup`
   - Documentação atualizada

## 🎉 Migração Concluída

A migração do **To The Future** para ambiente host-based foi concluída com sucesso! O novo ambiente oferece:

- 🚀 **Performance superior**
- 🛠️ **Ferramentas integradas**  
- 🔧 **Setup simplificado**
- 📝 **Melhor experiência de desenvolvimento**

**Happy Coding!** 🎯
