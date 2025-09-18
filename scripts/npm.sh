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
docker compose exec php npm "$@"
