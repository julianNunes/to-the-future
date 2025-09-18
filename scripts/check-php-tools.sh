#!/bin/bash

# Script para verificar PHP CS Fixer e Xdebug
# Uso: ./scripts/check-php-tools.sh

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== VERIFICAÇÃO DE FERRAMENTAS PHP ===${NC}"
echo ""

# Verificar se containers estão rodando
if ! docker compose ps | grep -q "php.*Up"; then
    echo -e "${RED}❌ Container PHP não está rodando${NC}"
    echo "Execute: ./start.sh dev"
    exit 1
fi

echo -e "${GREEN}✅ Container PHP está rodando${NC}"
echo ""

# Verificar versão do PHP
echo -e "${YELLOW}🐘 Verificando PHP...${NC}"
PHP_VERSION=$(docker compose exec php php -v | head -n 1)
echo -e "${GREEN}✅ $PHP_VERSION${NC}"
echo ""

# Verificar extensões PHP instaladas
echo -e "${YELLOW}🔧 Verificando extensões PHP...${NC}"
EXTENSIONS=$(docker compose exec php php -m)

if echo "$EXTENSIONS" | grep -q "Xdebug"; then
    echo -e "${GREEN}✅ Xdebug instalado${NC}"

    # Verificar configuração do Xdebug
    echo -e "${YELLOW}⚙️  Configuração do Xdebug:${NC}"
    docker compose exec php php -i | grep -A 10 -B 5 xdebug.mode || echo -e "${YELLOW}  Configurações não encontradas${NC}"
else
    echo -e "${RED}❌ Xdebug não encontrado${NC}"
fi

if echo "$EXTENSIONS" | grep -q "pdo_mysql"; then
    echo -e "${GREEN}✅ PDO MySQL instalado${NC}"
else
    echo -e "${RED}❌ PDO MySQL não encontrado${NC}"
fi

echo ""

# Verificar Composer
echo -e "${YELLOW}📦 Verificando Composer...${NC}"
COMPOSER_VERSION=$(docker compose exec php composer --version 2>/dev/null)
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ $COMPOSER_VERSION${NC}"
else
    echo -e "${RED}❌ Composer não encontrado${NC}"
fi

# Verificar se vendor existe
if docker compose exec php test -d vendor; then
    echo -e "${GREEN}✅ Dependências instaladas (pasta vendor existe)${NC}"
else
    echo -e "${YELLOW}⚠️  Dependências não instaladas${NC}"
    echo "Execute: ./scripts/composer.sh install"
fi

echo ""

# Verificar PHP CS Fixer
echo -e "${YELLOW}🎨 Verificando PHP CS Fixer...${NC}"

# Verificar se está no composer.json
if grep -q "friendsofphp/php-cs-fixer" composer.json; then
    echo -e "${GREEN}✅ PHP CS Fixer está no composer.json${NC}"
else
    echo -e "${RED}❌ PHP CS Fixer não está no composer.json${NC}"
fi

# Verificar se está instalado
if docker compose exec php test -f vendor/bin/php-cs-fixer; then
    echo -e "${GREEN}✅ PHP CS Fixer instalado${NC}"

    # Testar execução
    CS_FIXER_VERSION=$(docker compose exec php php vendor/bin/php-cs-fixer --version 2>/dev/null | head -n 1)
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ $CS_FIXER_VERSION${NC}"
    else
        echo -e "${YELLOW}⚠️  PHP CS Fixer instalado mas com problemas${NC}"
    fi
else
    echo -e "${RED}❌ PHP CS Fixer não instalado${NC}"
    echo "Execute: ./scripts/composer.sh install"
fi

# Verificar arquivo de configuração
if [ -f ".php-cs-fixer.php" ]; then
    echo -e "${GREEN}✅ Arquivo de configuração .php-cs-fixer.php existe${NC}"
else
    echo -e "${YELLOW}⚠️  Arquivo .php-cs-fixer.php não encontrado${NC}"
fi

echo ""

# Verificar Node.js e NPM
echo -e "${YELLOW}📦 Verificando Node.js...${NC}"
NODE_VERSION=$(docker compose exec php node --version 2>/dev/null)
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Node.js $NODE_VERSION${NC}"
else
    echo -e "${RED}❌ Node.js não encontrado${NC}"
fi

NPM_VERSION=$(docker compose exec php npm --version 2>/dev/null)
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ NPM $NPM_VERSION${NC}"
else
    echo -e "${RED}❌ NPM não encontrado${NC}"
fi

echo ""

# Verificar configuração de debug do VS Code
echo -e "${YELLOW}🐛 Verificando configuração de debug...${NC}"

if [ -f ".vscode/launch.json" ]; then
    echo -e "${GREEN}✅ Configuração de debug (.vscode/launch.json) existe${NC}"

    # Verificar se tem configuração do Xdebug
    if grep -q "xdebug" .vscode/launch.json; then
        echo -e "${GREEN}✅ Configuração do Xdebug encontrada${NC}"
    else
        echo -e "${YELLOW}⚠️  Configuração do Xdebug não encontrada no launch.json${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  Arquivo .vscode/launch.json não encontrado${NC}"
fi

if [ -f "docker/php/xdebug.ini" ]; then
    echo -e "${GREEN}✅ Configuração do Xdebug (docker/php/xdebug.ini) existe${NC}"
else
    echo -e "${YELLOW}⚠️  Arquivo docker/php/xdebug.ini não encontrado${NC}"
fi

echo ""
echo -e "${BLUE}=== COMANDOS ÚTEIS ===${NC}"
echo ""
echo -e "${YELLOW}Para usar PHP CS Fixer:${NC}"
echo "  ./scripts/php-cs-fixer.sh fix                 # Formatar código"
echo "  ./scripts/php-cs-fixer.sh fix --dry-run       # Apenas verificar"
echo ""
echo -e "${YELLOW}Para debug no VS Code:${NC}"
echo "  1. Coloque um breakpoint no código PHP"
echo "  2. Pressione F5 ou vá em Run > Start Debugging"
echo "  3. Selecione 'Listen for Xdebug'"
echo "  4. Acesse a página no navegador: http://localhost:8080"
echo ""
echo -e "${YELLOW}Para instalar dependências:${NC}"
echo "  ./scripts/composer.sh install    # PHP"
echo "  ./scripts/npm.sh install         # JavaScript"
echo ""
