#!/bin/bash

# Script para limpar e otimizar o Dev Container
echo "🧹 Limpando ambiente Dev Container..."

# Para todos os containers
echo "⏹️ Parando containers..."
docker compose down

# Remove containers órfãos e volumes não utilizados
echo "🗑️ Limpando recursos Docker..."
docker system prune -f
docker volume prune -f

# Limpa node_modules e vendor se necessário
if [ -d "node_modules" ]; then
    echo "📦 Limpando node_modules..."
    rm -rf node_modules
fi

if [ -d "vendor" ]; then
    echo "📦 Limpando vendor..."
    rm -rf vendor
fi

# Limpa caches Laravel
if [ -d "storage" ]; then
    echo "🧹 Limpando caches Laravel..."
    find storage/logs -name "*.log" -type f -delete 2>/dev/null || true
    find storage/framework/cache -name "*" -type f -delete 2>/dev/null || true
    find storage/framework/sessions -name "*" -type f -delete 2>/dev/null || true
    find storage/framework/views -name "*" -type f -delete 2>/dev/null || true
fi

echo "✅ Limpeza concluída!"
echo ""
echo "🚀 Para iniciar novamente:"
echo "   docker compose up -d --build"
echo ""
