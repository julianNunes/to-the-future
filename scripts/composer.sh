#!/bin/bash

# Script para executar Composer através do container Docker
# Uso: ./scripts/composer.sh [comandos do composer]

# Verificar se o container está rodando
if ! docker compose ps | grep -q "php.*Up"; then
    echo "⚠️  Container PHP não está rodando."
    echo "Execute: ./start.sh dev"
    exit 1
fi

# Executar Composer no container
docker compose exec php composer "$@"
