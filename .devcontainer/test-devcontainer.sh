#!/bin/bash

# 🧪 Script para testar Dev Container
# Autor: GitHub Copilot Assistant
# Data: $(date +"%Y-%m-%d")

set -e

echo "🧪 SCRIPT DE TESTE - DEV CONTAINER"
echo "=================================="
echo

# Verificar se estamos no Dev Container
if [ "$DEVCONTAINER" = "true" ] || [ -f "/.dockerenv" ]; then
    echo "✅ Executando dentro do Dev Container"
    echo
    
    echo "=== 1. TESTANDO FERRAMENTAS BÁSICAS ==="
    
    echo "🔍 PHP:"
    php --version | head -1
    
    echo "🔍 Node.js:"
    node --version
    
    echo "🔍 Composer:"
    composer --version | head -1
    
    echo "🔍 NPM:"
    npm --version
    
    echo
    echo "=== 2. TESTANDO CONECTIVIDADE ==="
    
    echo "🌐 Testando Laravel (8080):"
    # Testar tanto localhost quanto 127.0.0.1
    if curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 | grep -q "200" || \
       curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8080 | grep -q "200"; then
        echo "✅ Laravel respondendo"
    else
        echo "❌ Laravel não está respondendo"
        echo "   Tentando diagnosticar..."
        echo "   - Porta 8080: $(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 2>/dev/null || echo "ERRO")"
        echo "   - Containers: $(docker ps --format 'table {{.Names}}\t{{.Status}}' | grep -E '(nginx|php)' | head -2)"
    fi
    
    echo "🌐 Testando Vite (5173):"
    if curl -s -o /dev/null -w "%{http_code}" http://localhost:5173 | grep -q "200"; then
        echo "✅ Vite respondendo"
    else
        echo "❌ Vite não está respondendo"
    fi
    
    echo "🗄️ Testando MySQL (3307):"
    if nc -z localhost 3307 2>/dev/null; then
        echo "✅ MySQL acessível"
    else
        echo "❌ MySQL não acessível"
    fi
    
    echo
    echo "=== 3. VERIFICANDO MOUNTS DO COPILOT ==="
    
    if [ -d "/home/www-data/.config/github-copilot" ]; then
        echo "✅ Diretório github-copilot montado"
        ls -la /home/www-data/.config/github-copilot/ | head -3
    else
        echo "❌ Diretório github-copilot NÃO montado"
    fi
    
    if [ -d "/home/www-data/.vscode" ]; then
        echo "✅ Diretório .vscode montado"
    else
        echo "❌ Diretório .vscode NÃO montado"
    fi
    
    echo
    echo "=== 4. TESTANDO LARAVEL ==="
    
    echo "🎯 Testando Artisan:"
    if php artisan --version; then
        echo "✅ Artisan funcionando"
    else
        echo "❌ Artisan com problemas"
    fi
    
    echo
    echo "=== 5. ARQUIVOS ESSENCIAIS ==="
    
    for file in ".env" "vendor/autoload.php" "node_modules" "composer.json" "package.json"; do
        if [ -e "$file" ]; then
            echo "✅ $file existe"
        else
            echo "❌ $file não encontrado"
        fi
    done
    
    echo
    echo "🎉 TESTE CONCLUÍDO!"
    echo
    echo "📋 PRÓXIMOS PASSOS:"
    echo "1. Abrir http://localhost:8080 no navegador"
    echo "2. Testar GitHub Copilot (Ctrl+I)"
    echo "3. Executar: php artisan migrate"
    
else
    echo "❌ Este script deve ser executado DENTRO do Dev Container"
    echo
    echo "📋 INSTRUÇÕES:"
    echo "1. Abra o Command Palette (Ctrl+Shift+P)"
    echo "2. Digite: 'Dev Containers: Reopen in Container'"
    echo "3. Aguarde o container inicializar"
    echo "4. Execute este script novamente"
fi
