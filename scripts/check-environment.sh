#!/bin/bash

# Script para verificar se o ambiente está corretamente configurado
# Uso: ./scripts/check-environment.sh

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== VERIFICAÇÃO DO AMBIENTE TO THE FUTURE ===${NC}"
echo ""

# Função para verificar se um comando existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Função para verificar se arquivo existe e tem permissão de execução
check_executable() {
    if [ -f "$1" ] && [ -x "$1" ]; then
        echo -e "${GREEN}✅ $1 (executável)${NC}"
        return 0
    elif [ -f "$1" ]; then
        echo -e "${YELLOW}⚠️  $1 (existe mas não é executável)${NC}"
        return 1
    else
        echo -e "${RED}❌ $1 (não encontrado)${NC}"
        return 2
    fi
}

# Verificar pré-requisitos
echo -e "${YELLOW}🔍 Verificando pré-requisitos...${NC}"

if command_exists docker; then
    echo -e "${GREEN}✅ Docker$(docker --version | cut -d' ' -f3)${NC}"
else
    echo -e "${RED}❌ Docker não encontrado${NC}"
fi

if command_exists docker-compose || docker compose version >/dev/null 2>&1; then
    echo -e "${GREEN}✅ Docker Compose${NC}"
else
    echo -e "${RED}❌ Docker Compose não encontrado${NC}"
fi

if command_exists code; then
    echo -e "${GREEN}✅ VS Code${NC}"
else
    echo -e "${YELLOW}⚠️  VS Code não encontrado (opcional)${NC}"
fi

if command_exists git; then
    echo -e "${GREEN}✅ Git${NC}"
else
    echo -e "${RED}❌ Git não encontrado${NC}"
fi

echo ""

# Verificar scripts
echo -e "${YELLOW}📁 Verificando scripts...${NC}"
check_executable "./start.sh"
check_executable "./scripts/php.sh"
check_executable "./scripts/composer.sh"
check_executable "./scripts/artisan.sh"
check_executable "./scripts/php-cs-fixer.sh"
check_executable "./scripts/npm.sh"
check_executable "./scripts/install-vscode-extensions.sh"

echo ""

# Verificar arquivos de configuração
echo -e "${YELLOW}⚙️  Verificando configurações...${NC}"

if [ -f "to-the-future.code-workspace" ]; then
    echo -e "${GREEN}✅ Workspace VS Code${NC}"
else
    echo -e "${RED}❌ Workspace VS Code não encontrado${NC}"
fi

if [ -f ".env" ]; then
    echo -e "${GREEN}✅ Arquivo .env${NC}"
    # Verificar configurações Docker
    if grep -q "DB_HOST=mysql" .env; then
        echo -e "${GREEN}  ✅ DB_HOST configurado para Docker${NC}"
    else
        echo -e "${YELLOW}  ⚠️  DB_HOST não está configurado para Docker${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  Arquivo .env não encontrado${NC}"
fi

if [ -f "docker-compose.yml" ]; then
    echo -e "${GREEN}✅ Docker Compose${NC}"
else
    echo -e "${RED}❌ Docker Compose não encontrado${NC}"
fi

if [ -f ".vscode/extensions.json" ]; then
    echo -e "${GREEN}✅ Extensões recomendadas VS Code${NC}"
else
    echo -e "${YELLOW}⚠️  Extensões recomendadas não encontradas${NC}"
fi

echo ""

# Verificar status do Docker
echo -e "${YELLOW}🐳 Verificando Docker...${NC}"

if command_exists docker; then
    if docker info >/dev/null 2>&1; then
        echo -e "${GREEN}✅ Docker daemon rodando${NC}"

        # Verificar se containers estão rodando
        if docker compose ps 2>/dev/null | grep -q "Up"; then
            echo -e "${GREEN}✅ Containers do projeto rodando${NC}"
        else
            echo -e "${YELLOW}⚠️  Containers não estão rodando (use: ./start.sh dev)${NC}"
        fi
    else
        echo -e "${RED}❌ Docker daemon não está rodando${NC}"
    fi
fi

echo ""

# Verificar documentação
echo -e "${YELLOW}📚 Verificando documentação...${NC}"

if [ -f "README-DEVELOPMENT.md" ]; then
    echo -e "${GREEN}✅ Documentação de desenvolvimento${NC}"
else
    echo -e "${RED}❌ Documentação de desenvolvimento não encontrada${NC}"
fi

if [ -f "readme.md" ]; then
    echo -e "${GREEN}✅ README principal${NC}"
else
    echo -e "${RED}❌ README principal não encontrado${NC}"
fi

echo ""
echo -e "${BLUE}=== RESUMO ===${NC}"
echo -e "${GREEN}Se todos os itens estão ✅, o ambiente está pronto!${NC}"
echo -e "${YELLOW}Itens ⚠️  são opcionais ou podem ser corrigidos facilmente${NC}"
echo -e "${RED}Itens ❌ precisam ser resolvidos antes de continuar${NC}"
echo ""
echo -e "${YELLOW}💡 Para configurar o ambiente, execute: ${GREEN}./start.sh setup${NC}"
