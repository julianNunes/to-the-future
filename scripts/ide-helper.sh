#!/bin/bash

# Script para gerar arquivos IDE Helper do Laravel
# Melhora IntelliSense sem poluir os models originais

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Verificar se foi passado um comando
if [ $# -eq 0 ]; then
    echo -e "${BLUE}=== LARAVEL IDE HELPER ===${NC}"
    echo ""
    echo "Uso: ./scripts/ide-helper.sh [comando]"
    echo ""
    echo -e "${YELLOW}📋 Comandos disponíveis:${NC}"
    echo ""
    echo "  all                      - Gera todos os arquivos helper"
    echo "  models                   - Gera helper dos models (sem modificar originais)"
    echo "  facades                  - Gera helper das facades"
    echo "  meta                     - Gera meta para PHPStorm"
    echo "  clean                    - Remove arquivos helper gerados"
    echo ""
    echo -e "${YELLOW}💡 Exemplos:${NC}"
    echo "  ./scripts/ide-helper.sh all      # Recomendado para setup"
    echo "  ./scripts/ide-helper.sh models   # Apenas após mudanças nos models"
    echo ""
    echo -e "${BLUE}📁 Arquivos que serão gerados:${NC}"
    echo "• _ide_helper.php - Facades e helpers gerais"
    echo "• _ide_helper_models.php - Models (não modifica originais)"
    echo "• .phpstorm.meta.php - Meta para PHPStorm"
    echo ""
    echo -e "${YELLOW}⚠️  Configuração atual (config/ide-helper.php):${NC}"
    echo "• write_model_magic_where: false (não polui models)"
    echo "• write_eloquent_model_mixins: false (não polui models)"
    echo "• model_locations: app/"
    exit 0
fi

COMMAND=$1

# Verificar se o ambiente está rodando
if ! docker compose ps | grep -q "php.*Up"; then
    echo -e "${RED}❌ Ambiente Docker não está rodando!${NC}"
    echo "Execute: ./start.sh dev"
    exit 1
fi

# Diretório do script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

case $COMMAND in
    "all")
        echo -e "${BLUE}🧠 Gerando todos os arquivos IDE Helper...${NC}"
        echo ""

        echo -e "${YELLOW}1/3 Gerando helpers das facades...${NC}"
        "$SCRIPT_DIR/artisan.sh" ide-helper:generate

        echo ""
        echo -e "${YELLOW}2/3 Gerando helpers dos models (sem modificar originais)...${NC}"
        "$SCRIPT_DIR/artisan.sh" ide-helper:models --nowrite

        echo ""
        echo -e "${YELLOW}3/3 Gerando meta para PHPStorm...${NC}"
        "$SCRIPT_DIR/artisan.sh" ide-helper:meta

        echo ""
        echo -e "${GREEN}✅ Todos os arquivos IDE Helper gerados com sucesso!${NC}"
        echo ""
        echo -e "${BLUE}📁 Arquivos gerados:${NC}"
        ls -la "$PROJECT_ROOT"/_ide_helper* "$PROJECT_ROOT"/.phpstorm.meta.php 2>/dev/null || echo "  (verificando...)"
        echo ""
        echo -e "${YELLOW}💡 Reinicie o VS Code para aplicar as mudanças no IntelliSense${NC}"
        ;;

    "models")
        echo -e "${BLUE}🧠 Gerando helper dos models...${NC}"
        "$SCRIPT_DIR/artisan.sh" ide-helper:models --nowrite
        echo ""
        echo -e "${GREEN}✅ Helper dos models gerado: _ide_helper_models.php${NC}"
        echo -e "${YELLOW}💡 Seus models originais não foram modificados${NC}"
        ;;

    "facades")
        echo -e "${BLUE}🧠 Gerando helper das facades...${NC}"
        "$SCRIPT_DIR/artisan.sh" ide-helper:generate
        echo ""
        echo -e "${GREEN}✅ Helper das facades gerado: _ide_helper.php${NC}"
        ;;

    "meta")
        echo -e "${BLUE}🧠 Gerando meta para PHPStorm...${NC}"
        "$SCRIPT_DIR/artisan.sh" ide-helper:meta
        echo ""
        echo -e "${GREEN}✅ Meta gerado: .phpstorm.meta.php${NC}"
        ;;

    "clean")
        echo -e "${YELLOW}🧹 Removendo arquivos IDE Helper gerados...${NC}"

        cd "$PROJECT_ROOT" || exit 1

        if [ -f "_ide_helper.php" ]; then
            rm "_ide_helper.php"
            echo -e "${GREEN}✅ Removido: _ide_helper.php${NC}"
        fi

        if [ -f "_ide_helper_models.php" ]; then
            rm "_ide_helper_models.php"
            echo -e "${GREEN}✅ Removido: _ide_helper_models.php${NC}"
        fi

        if [ -f ".phpstorm.meta.php" ]; then
            rm ".phpstorm.meta.php"
            echo -e "${GREEN}✅ Removido: .phpstorm.meta.php${NC}"
        fi

        echo ""
        echo -e "${GREEN}✅ Limpeza concluída${NC}"
        echo -e "${YELLOW}💡 Execute './scripts/ide-helper.sh all' para regenerar${NC}"
        ;;

    *)
        echo -e "${RED}❌ Comando não reconhecido: $COMMAND${NC}"
        echo "Use './scripts/ide-helper.sh' sem parâmetros para ver a ajuda"
        exit 1
        ;;
esac
