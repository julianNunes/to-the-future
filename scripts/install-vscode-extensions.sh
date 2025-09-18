#!/bin/bash

# Script para instalar extensões do VS Code para o projeto To The Future
# Baseado nas extensões configuradas no Dev Container

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== TO THE FUTURE - INSTALADOR DE EXTENSÕES VS CODE ===${NC}"
echo ""

# Verificar se o VS Code está instalado
if ! command -v code &> /dev/null; then
    echo -e "${RED}❌ VS Code não está instalado ou não está no PATH${NC}"
    echo ""
    echo -e "${YELLOW}💡 Instalar VS Code:${NC}"
    echo "1. Baixe em: https://code.visualstudio.com/"
    echo "2. Ou use: sudo snap install code --classic"
    echo "3. Ou use: sudo apt install code"
    exit 1
fi

echo -e "${GREEN}✅ VS Code encontrado${NC}"
echo ""

# Lista de extensões extraídas do devcontainer.json
extensions=(
    # === CORE ESSENTIALS ===
    "GitHub.copilot"                        # IA Assistant
    "GitHub.copilot-chat"                   # Chat IA
    "bmewburn.vscode-intelephense-client"   # PHP IntelliSense
    
    # === LARAVEL STACK ===
    "open-southeners.laravel-pint"         # Laravel Pint (PHP Formatter)
    "codingyu.laravel-goto-view"           # Laravel Navigation (útil para Inertia)
    "barryvdh.laravel-ide-helper"          # Laravel IDE Helper (resolve namespaces)
    
    # === VUE.JS 3 + INERTIA + VUETIFY ===
    "Vue.volar"                            # Vue 3 Official (Language Server)
    
    # === FORMATTERS & TOOLS ===
    "esbenp.prettier-vscode"               # JS/CSS/Vue Formatter
    "eamodio.gitlens"                      # Git Enhanced
    
    # === UTILITIES ===
    "ms-vscode.vscode-json"                # JSON Support
    "DotJoshJohnson.xml"                   # XML Support
    
    # === EXTENSÕES ADICIONAIS RECOMENDADAS ===
    "junstyle.php-cs-fixer"               # PHP CS Fixer
    "ryannaddy.laravel-artisan"           # Laravel Artisan Commands
    "onecentlin.laravel-blade"            # Laravel Blade Syntax
    "ms-azuretools.vscode-docker"         # Docker Support
    "formulahendry.auto-rename-tag"       # Auto Rename HTML Tags
    "christian-kohler.path-intellisense"  # Path Intellisense
    "bradlc.vscode-tailwindcss"          # Tailwind CSS IntelliSense
    "ms-vscode.vscode-typescript-next"    # TypeScript Support
    "ms-vscode.vscode-eslint"             # ESLint
)

echo -e "${YELLOW}📦 Instalando ${#extensions[@]} extensões...${NC}"
echo ""

# Contador de sucessos e falhas
success_count=0
failed_extensions=()

# Instalar cada extensão
for extension in "${extensions[@]}"; do
    # Pular comentários
    if [[ $extension =~ ^[[:space:]]*# ]]; then
        continue
    fi
    
    echo -e "${BLUE}Instalando: ${extension}${NC}"
    
    if code --install-extension "$extension" --force > /dev/null 2>&1; then
        echo -e "${GREEN}✅ $extension${NC}"
        ((success_count++))
    else
        echo -e "${RED}❌ $extension${NC}"
        failed_extensions+=("$extension")
    fi
done

echo ""
echo -e "${BLUE}=== RELATÓRIO DE INSTALAÇÃO ===${NC}"
echo ""
echo -e "${GREEN}✅ Extensões instaladas com sucesso: $success_count${NC}"

if [ ${#failed_extensions[@]} -gt 0 ]; then
    echo -e "${RED}❌ Extensões que falharam: ${#failed_extensions[@]}${NC}"
    echo ""
    echo -e "${YELLOW}Extensões que falharam:${NC}"
    for failed in "${failed_extensions[@]}"; do
        echo "  - $failed"
    done
    echo ""
    echo -e "${YELLOW}💡 Você pode tentar instalar manualmente:${NC}"
    for failed in "${failed_extensions[@]}"; do
        echo "code --install-extension $failed"
    done
else
    echo -e "${GREEN}🎉 Todas as extensões foram instaladas com sucesso!${NC}"
fi

echo ""
echo -e "${BLUE}📝 Próximos passos:${NC}"
echo "1. Abra o VS Code: ${GREEN}code to-the-future.code-workspace${NC}"
echo "2. Reinicie o VS Code para ativar todas as extensões"
echo "3. Configure o workspace se necessário"
echo ""
echo -e "${YELLOW}⚠️  Nota sobre GitHub Copilot:${NC}"
echo "Se você usar GitHub Copilot, faça login na primeira execução:"
echo "1. Pressione Ctrl+Shift+P"
echo "2. Digite 'GitHub Copilot: Sign In'"
echo "3. Siga as instruções na tela"
echo ""
echo -e "${GREEN}✨ Instalação de extensões concluída!${NC}"