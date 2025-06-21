#!/bin/bash

echo "Iniciando entrypoint..."

# Cria o link simbólico para o storage (se ainda não existir)
php artisan storage:link || echo "Link de storage já existe ou falhou."

# Executa as migration automaticamente (opcional – remova se preferir executar manualmente)
php artisan migrate --force || echo "Não foi possível executar as migrations. Continuando..."

# Gera os caches de otimização com as variáveis de ambiente corretas do ambiente de execução.
echo "Gerando caches de otimização do Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Entrypoint concluído. Iniciando Apache..."
exec "$@"
