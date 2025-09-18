#!/bin/bash

# Script para executar Artisan através do container Docker
# Uso: ./scripts/artisan.sh [comandos do artisan]

# Verificar se o container está rodando
if ! docker compose ps | grep -q "php.*Up"; then
    echo "⚠️  Container PHP não está rodando."
    echo "Execute: ./start.sh dev"
    exit 1
fi

# Executar Artisan no container
docker compose exec php php artisan "$@"
