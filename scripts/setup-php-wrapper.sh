#!/bin/bash

# Script para configurar PHP wrapper para Intelephense
# Este script configura o ambiente para que o Intelephense use o PHP do container

echo "🔧 Configurando PHP wrapper para Intelephense..."

# Determinar diretórios automaticamente
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
WRAPPER_SCRIPT="$PROJECT_ROOT/scripts/php-wrapper.sh"

# Verificar se o wrapper existe
if [ ! -f "$WRAPPER_SCRIPT" ]; then
    echo "❌ Wrapper script não encontrado: $WRAPPER_SCRIPT"
    exit 1
fi

# Tornar executável
chmod +x "$WRAPPER_SCRIPT"

# Criar diretório ~/.local/bin se não existir
mkdir -p ~/.local/bin

# Criar symlink específico do projeto
PROJECT_NAME=$(basename "$PROJECT_ROOT")
ln -sf "$WRAPPER_SCRIPT" ~/.local/bin/php-$PROJECT_NAME

# Verificar se ~/.local/bin está no PATH
if [[ ":$PATH:" != *":$HOME/.local/bin:"* ]]; then
    echo ""
    echo "⚠️  IMPORTANTE: Adicione ~/.local/bin ao seu PATH"
    echo ""
    echo "Adicione esta linha ao seu ~/.bashrc ou ~/.zshrc:"
    echo 'export PATH="$HOME/.local/bin:$PATH"'
    echo ""
    echo "Depois execute: source ~/.bashrc"
    echo ""
fi

echo ""
echo "✅ Configuração concluída!"
echo ""
echo "📋 Configuração aplicada:"
echo ""
echo "1️⃣  Configuração do VS Code Workspace:"
echo "   • O arquivo workspace foi atualizado automaticamente"
echo "   • Todas as configurações estão centralizadas no workspace"
echo "   • Reinicie o VS Code para aplicar as configurações"
echo ""
echo "2️⃣  PHP wrapper configurado:"
echo "   • Script: $WRAPPER_SCRIPT"
echo "   • Symlink criado: ~/.local/bin/php-$PROJECT_NAME"
echo "   • Wrapper detecta automaticamente o diretório do projeto"
echo ""
echo "3️⃣  Integração com start.sh:"
echo "   • Comando disponível: ./start.sh setup-php-wrapper"
echo "   • Task do VS Code: 'Setup: PHP Wrapper for Intelephense'"
echo ""
echo "🚀 Teste o wrapper:"
echo "   $WRAPPER_SCRIPT --version"
echo ""
echo "🔄 Reinicie o VS Code para aplicar todas as configurações!"
