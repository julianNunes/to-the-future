# Configuração PHP para Intelephense com Docker

Este documento explica como configurar o Intelephense para usar o PHP do container Docker sem precisar do Dev Container.

## 🎯 Problema

O Intelephense precisa de acesso ao executável PHP no host para:

- Validação de sintaxe
- Autocompletar código
- Análise estática
- IntelliSense completo

## 🔧 Solução Integrada

### Configuração Automática (RECOMENDADO)

Tudo foi integrado no workspace e no sistema de scripts:

**Configuração inicial:**

```bash
# Setup completo (inclui wrapper PHP)
./start.sh setup

# Ou apenas o wrapper PHP
./start.sh setup-php-wrapper
```

**Teste:**

```bash
# Testar wrapper
./start.sh test-php-wrapper
```

## 📋 Como Funciona

### 1. Workspace Centralizado

- **Arquivo único**: `nova-central-v41.code-workspace`
- **Remoção**: `.vscode/settings.json` foi removido
- **Configuração**: Tudo centralizado no workspace

### 2. Scripts Inteligentes

- **Auto-detecção**: Scripts detectam diretório do projeto automaticamente
- **Portabilidade**: Funciona em qualquer local/usuário
- **Integração**: 100% integrado ao `start.sh`

### 3. Wrapper PHP

O script `php-wrapper.sh`:

- Detecta automaticamente o diretório do projeto
- Verifica se o container está rodando
- Converte paths locais para paths do container
- Executa PHP no container com contexto correto

## 🚀 Comandos Disponíveis

```bash
# Setup completo
./start.sh setup

# Configurar apenas wrapper PHP
./start.sh setup-php-wrapper

# Testar wrapper PHP
./start.sh test-php-wrapper

# Verificar container
./start.sh status

# Iniciar ambiente
./start.sh dev
```

## 🏗️ Tasks do VS Code

No workspace estão disponíveis as tasks:

- **Setup: PHP Wrapper for Intelephense**
- **Laravel: Start Development**
- **IDE Helper: Generate All**

## 🔍 Troubleshooting

### Container não está rodando

```bash
./start.sh dev
```

### Wrapper não funciona

```bash
# Verificar e reconfigurar
./start.sh setup-php-wrapper

# Testar
./start.sh test-php-wrapper
```

### Intelephense ainda não funciona

1. Reinicie o VS Code completamente
2. Abra o workspace: `code nova-central-v41.code-workspace`
3. Use Command Palette: "PHP: Restart PHP Language Server"

## 📁 Arquivos da Solução

- `nova-central-v41.code-workspace` - Configurações centralizadas
- `scripts/php-wrapper.sh` - Wrapper principal
- `scripts/setup-php-wrapper.sh` - Script de configuração
- `start.sh` - Sistema integrado de comandos

## 🎯 Vantagens

✅ **Configuração única no workspace**
✅ **Scripts auto-detectam projeto**
✅ **100% integrado ao start.sh**
✅ **Portável entre usuários**
✅ **Não precisa instalar PHP no host**
✅ **Versão correta do PHP (7.4)**
✅ **Inclui todas as extensões do container**

## 🔄 Migração

A solução anterior foi **completamente integrada**:

- ✅ Tudo no workspace centralizado
- ✅ Scripts genéricos (não hardcoded)
- ✅ Integração total com start.sh
