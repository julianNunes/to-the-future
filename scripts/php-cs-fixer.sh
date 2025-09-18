#!/bin/bash

# Script para executar PHP CS Fixer através do container Docker
# Uso: ./scripts/php-cs-fixer.sh [argumentos do php-cs-fixer]

# Verificar se o container está rodando
if ! docker compose ps | grep -q "php.*Up"; then
    echo "⚠️  Container PHP não está rodando."
    echo "Execute: ./start.sh dev"
    exit 1
fi

# Detecta se o último argumento é um arquivo PHP
ARGS=("$@")
LAST_ARG="${ARGS[-1]}"

# Se o último argumento for um arquivo PHP, converte o path do host para o path do container
if [[ "$LAST_ARG" == *.php && -f "$LAST_ARG" ]]; then
    # Obter o diretório do projeto dinamicamente
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
    
    # Substitui o path do host pelo path do container
    CONTAINER_PATH="${LAST_ARG/$PROJECT_ROOT/\/var\/www\/html}"
    # Remove o último argumento
    unset 'ARGS[-1]'
    # Adiciona o path convertido
    ARGS+=("$CONTAINER_PATH")
fi

# Executa o fixer no container
docker compose exec php php vendor/bin/php-cs-fixer "${ARGS[@]}"
