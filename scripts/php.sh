#!/bin/bash

# Script para executar PHP através do container Docker
# Uso: ./scripts/php.sh [argumentos do php]

# Verificar se o container está rodando
if ! docker compose ps | grep -q "php.*Up"; then
    echo "⚠️  Container PHP não está rodando."
    echo "Execute: ./start.sh dev"
    exit 1
fi

# Executar PHP no container
docker compose exec php php "$@"
