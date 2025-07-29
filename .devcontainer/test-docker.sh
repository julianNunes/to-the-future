#!/bin/bash

# Script para testar se o Docker funciona dentro do Dev Container
echo "🐳 Testando configuração Docker no Dev Container..."

# Verifica se o Docker CLI está instalado
if command -v docker &> /dev/null; then
    echo "✅ Docker CLI encontrado: $(docker --version)"
else
    echo "❌ Docker CLI não encontrado!"
    exit 1
fi

# Verifica se o socket do Docker está acessível
if [ -S /var/run/docker.sock ]; then
    echo "✅ Socket do Docker encontrado: /var/run/docker.sock"
else
    echo "❌ Socket do Docker não encontrado!"
    exit 1
fi

# Testa se consegue acessar o Docker daemon
if docker info &> /dev/null; then
    echo "✅ Conexão com Docker daemon funcionando!"
    echo "📊 Containers rodando:"
    docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
else
    echo "❌ Não consegue se conectar ao Docker daemon!"
    echo "ℹ️ Isso pode ser normal se o docker compose não estiver rodando ainda."
fi

echo ""
echo "🚀 Teste completo!"
