#!/bin/bash

# Script para executar NPM através do container Docker
# Uso: ./scripts/npm.sh [comandos do npm]

# Verificar se o container está rodando
if ! docker compose ps | grep -q "php.*Up"; then
    echo "⚠️  Container PHP não está rodando."
    echo "Execute: ./start.sh dev"
    exit 1
fi

# Executar NPM no container
if [ -t 0 ] && [ -t 1 ]; then
    docker compose exec php npm "$@"
else
    docker compose exec -T php npm "$@"
fi
