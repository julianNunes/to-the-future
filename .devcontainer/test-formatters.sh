#!/bin/bash

# 🎨 Script para testar formatadores
# Autor: GitHub Copilot Assistant
# Data: $(date +"%Y-%m-%d")

echo "🎨 TESTE DE FORMATADORES"
echo "======================="
echo

echo "=== 1. VERIFICANDO CONFIGURAÇÕES ==="

# Verificar se arquivos de configuração existem
echo "📋 Arquivos de configuração:"
for file in ".prettierrc" ".editorconfig" "pint.json"; do
    if [ -f "$file" ]; then
        echo "✅ $file existe"
    else
        echo "❌ $file não encontrado"
    fi
done

echo
echo "=== 2. VERIFICANDO PRETTIER ==="

if [ -f "node_modules/.bin/prettier" ]; then
    echo "📦 Prettier instalado"
    echo "🔧 Configuração do Prettier:"
    cat .prettierrc | head -8
    
    echo
    echo "🧪 Testando formatação de um arquivo Vue (se existir):"
    if find resources/js -name "*.vue" | head -1 | read file; then
        echo "🎯 Testando: $file"
        echo "Resultado: $(npx prettier --check "$file" 2>&1 || echo "Precisa de formatação")"
    else
        echo "⚠️ Nenhum arquivo Vue encontrado para teste"
    fi
else
    echo "❌ Prettier não instalado"
fi

echo
echo "=== 3. VERIFICANDO LARAVEL PINT ==="

if [ -f "vendor/bin/pint" ]; then
    echo "📦 Laravel Pint instalado"
    echo "🔧 Configuração do Pint:"
    cat pint.json | head -8
    
    echo
    echo "🧪 Testando formatação PHP (dry-run):"
    php vendor/bin/pint --test --preset=laravel app/Models/User.php 2>/dev/null && echo "✅ Arquivo PHP bem formatado" || echo "⚠️ Arquivo precisa de formatação"
else
    echo "❌ Laravel Pint não instalado"
fi

echo
echo "=== 4. VERIFICANDO VS CODE SETTINGS ==="

if [ -f ".devcontainer/settings.json" ]; then
    echo "📋 Configurações do Dev Container:"
    echo "- Formatação automática: $(grep -o '"editor.formatOnSave": [^,]*' .devcontainer/settings.json)"
    echo "- Tab size: $(grep -o '"editor.tabSize": [^,]*' .devcontainer/settings.json)"
    echo "- Espaços: $(grep -o '"editor.insertSpaces": [^,]*' .devcontainer/settings.json)"
else
    echo "❌ Configurações do Dev Container não encontradas"
fi

echo
echo "🎉 TESTE CONCLUÍDO!"
echo
echo "📋 PRÓXIMOS PASSOS:"
echo "1. Abrir um arquivo .vue e pressionar Ctrl+Shift+P → 'Format Document'"
echo "2. Abrir um arquivo .php e salvar (formatação automática)"
echo "3. Verificar se a indentação está com 4 espaços"
