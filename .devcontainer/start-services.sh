#!/bin/bash

echo "🚀 INICIANDO SERVIÇOS - Dev Container"
echo "==================================="

# Verificar se estamos dentro do Dev Container
if [ "$REMOTE_CONTAINERS" = "true" ] || [ "$CODESPACES" = "true" ] || [ -f "/.dockerenv" ]; then
    echo "✅ Rodando dentro do Dev Container"
    
    # Aguardar que todos os serviços estejam prontos
    echo "⏳ Aguardando serviços..."
    
    # Aguardar MySQL
    echo "📊 Aguardando MySQL..."
    for i in {1..30}; do
        if nc -z db 3306 2>/dev/null; then
            echo "✅ MySQL está pronto!"
            break
        fi
        sleep 2
    done
    
    # Aguardar Nginx/PHP
    echo "🌐 Aguardando Laravel..."
    for i in {1..20}; do
        if curl -s http://nginx:80 > /dev/null 2>&1; then
            echo "✅ Laravel está pronto!"
            break
        fi
        sleep 2
    done
    
    # Verificar Vite
    echo "⚡ Verificando Vite..."
    if curl -s http://vite:5173 > /dev/null 2>&1; then
        echo "✅ Vite está rodando!"
    else
        echo "⚠️ Vite não responde - pode estar inicializando"
    fi
    
    echo ""
    echo "🎯 SERVIÇOS DISPONÍVEIS:"
    echo "  📱 Laravel: http://localhost:8080"
    echo "  ⚡ Vite: http://localhost:5173"
    echo "  🗄️ MySQL: localhost:3307"
    echo ""
    echo "✅ Dev Container pronto para desenvolvimento!"
    
else
    echo "ℹ️ Rodando no HOST - iniciando containers Docker..."
    docker compose up -d
    echo "✅ Containers iniciados!"
fi
