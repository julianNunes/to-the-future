# 🚀 Guia de Início Rápido - To The Future

## 📋 Pré-requisitos

Certifique-se de ter instalado:

```bash
# Verificar se estão instalados:
docker --version          # Docker 20.10+
docker compose version    # Docker Compose 2.0+
code --version            # VS Code (recomendado)
git --version             # Git
```

## ⚡ Setup em 3 Passos

### 1. Clone e Configure

```bash
# Clone o repositório
git clone <URL_DO_REPOSITORIO>
cd to-the-future

# Setup automático completo (instala tudo)
./start.sh setup
```

### 2. Abra o VS Code

```bash
# Abrir workspace pré-configurado
code to-the-future.code-workspace
```

### 3. Inicie o Desenvolvimento

```bash
# Iniciar ambiente Docker
./start.sh dev

# Acesse: http://localhost:8080
```

## ✅ Verificação

Após o setup, verifique se tudo está funcionando:

```bash
# Verificar status dos containers
./start.sh status

# Testar comandos Laravel
./scripts/artisan.sh --version

# Testar Composer
./scripts/composer.sh --version

# Testar NPM
./scripts/npm.sh --version
```

## 🎯 Próximos Passos

1. **Leia a [Arquitetura do Projeto](02-architecture.md)** para entender como o sistema funciona
2. **Configure seu [Ambiente de Desenvolvimento](03-development-environment.md)**
3. **Aprenda os [Comandos Essenciais](04-essential-commands.md)**
4. **Configure [IntelliSense](06-intelephense-setup.md)** para melhor experiência de desenvolvimento

## 🆘 Problemas?

- **Containers não sobem**: Execute `./start.sh clean` e tente novamente
- **Extensões VS Code não funcionam**: Execute `./start.sh install-extensions`
- **Erros de permissão**: Execute `./start.sh fix-permissions`
- **Mais ajuda**: Consulte a [Resolução de Problemas](06-troubleshooting.md)

---

**⏱️ Tempo total do setup: ~5-10 minutos**

**🎉 Ambiente pronto para desenvolvimento!**
