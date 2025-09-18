#!/bin/bash

echo "🔧 Corrigindo permissões do Laravel..."

# Verificar se os containers estão rodando
if ! docker ps | grep -q "to-the-future-php"; then
    echo "❌ Container PHP não está rodando!"
    echo "Execute: ./start.sh dev"
    exit 1
fi

# Executar correção de permissões no container
docker exec to-the-future-php-1 bash -c "
    echo '📁 Criando diretórios básicos do Laravel...'
    mkdir -p /var/www/html/storage/logs
    mkdir -p /var/www/html/storage/framework/cache
    mkdir -p /var/www/html/storage/framework/sessions
    mkdir -p /var/www/html/storage/framework/views
    mkdir -p /var/www/html/bootstrap/cache

    echo '📂 Criando diretórios específicos da aplicação...'
    mkdir -p /var/www/html/storage/app/public

    echo '👤 Configurando propriedade dos arquivos...'
    chown -R www-data:www-data /var/www/html/storage
    chown -R www-data:www-data /var/www/html/bootstrap/cache

    echo '🔒 Configurando permissões...'
    chmod -R 775 /var/www/html/storage
    chmod -R 775 /var/www/html/bootstrap/cache

    echo '📋 Verificando diretórios criados:'
    ls -la /var/www/html/storage/app/public/

    echo '🧹 Limpando caches...'
    if [ -f /var/www/html/artisan ]; then
        php /var/www/html/artisan cache:clear 2>/dev/null || echo 'Cache clear falhou, mas não é crítico'
        php /var/www/html/artisan config:clear 2>/dev/null || echo 'Config clear falhou, mas não é crítico'
        php /var/www/html/artisan view:clear 2>/dev/null || echo 'View clear falhou, mas não é crítico'
    fi

    echo '✅ Permissões corrigidas com sucesso!'
"

echo ""
echo "🌐 Teste o acesso em: http://localhost:8080"
