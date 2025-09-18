#!/bin/bash

# PHP Wrapper para usar PHP do container Docker como se fosse local
# Este script permite que ferramentas como Intelephense usem o PHP do container

# Determinar o diretório do projeto automaticamente
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
CONTAINER_NAME="php"

# Verificar se estamos no diretório do projeto ou subdiretório
CURRENT_DIR=$(pwd)
if [[ "$CURRENT_DIR" == "$PROJECT_ROOT"* ]]; then
    # Estamos no projeto, usar o container

    # Verificar se o container está rodando
    if ! docker compose -f "$PROJECT_ROOT/docker-compose.yml" ps | grep -q "$CONTAINER_NAME.*Up"; then
        echo "⚠️  Container $CONTAINER_NAME não está rodando." >&2
        echo "Execute: cd $PROJECT_ROOT && ./start.sh dev" >&2
        exit 1
    fi

    # Converter path local para path do container
    CONTAINER_PATH=${CURRENT_DIR/#$PROJECT_ROOT/\/var\/www\/html}

    # Executar PHP no container com working directory correto
    docker compose -f "$PROJECT_ROOT/docker-compose.yml" exec -w "$CONTAINER_PATH" php php "$@"
else
    # Não estamos no projeto, tentar usar PHP local (se existir)
    if command -v php &> /dev/null; then
        /usr/bin/php "$@"
    else
        echo "⚠️  PHP não encontrado. Para usar este projeto, execute no diretório: $PROJECT_ROOT" >&2
        exit 1
    fi
fi
