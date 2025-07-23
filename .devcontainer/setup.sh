#!/bin/bash

# Script de inicialização do Dev Container
# Este script garante que o ambiente esteja configurado corretamente

echo "🚀 Iniciando configuração do Dev Container..."

# Aguarda os serviços estarem prontos
echo "⏳ Aguardando serviços..."
until nc -z 127.0.0.1 3307; do
    echo "Aguardando MySQL na porta 3307..."
    sleep 2
done

# Configura permissões
echo "🔧 Configurando permissões..."
sudo chown -R www-data:www-data /var/www/html/storage
sudo chown -R www-data:www-data /var/www/html/bootstrap/cache
sudo chown -R www-data:www-data /var/www/html/node_modules
sudo chmod -R 775 /var/www/html/storage
sudo chmod -R 775 /var/www/html/bootstrap/cache

# Instala dependências PHP se necessário
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "📦 Instalando dependências PHP..."
    composer install --no-interaction
fi

# Instala dependências Node.js se necessário
if [ ! -d "node_modules" ] || [ ! -f "package-lock.json" ]; then
    echo "📦 Instalando dependências Node.js..."
    npm install
fi

# Cria arquivo .env se não existir
if [ ! -f ".env" ]; then
    echo "⚙️ Criando arquivo .env..."
    cp .env.example .env
    php artisan key:generate --force
fi

# Limpa cache do Laravel
echo "🧹 Limpando cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Executa migrações se necessário
echo "🗄️ Verificando banco de dados..."
php artisan migrate --force 2>/dev/null || echo "ℹ️ Nenhuma migração encontrada ou erro na conexão - isso é normal em projetos novos"

# Gera arquivos de ajuda do IDE se as dependências estiverem instaladas
if composer show | grep -q "barryvdh/laravel-ide-helper"; then
    echo "🧠 Gerando arquivos de ajuda do IDE..."
    php artisan ide-helper:generate 2>/dev/null || echo "ℹ️ IDE Helper não configurado ainda"
    php artisan ide-helper:meta 2>/dev/null || echo "ℹ️ IDE Helper Meta não disponível"
    php artisan ide-helper:models --write-mixin --reset 2>/dev/null || echo "ℹ️ IDE Helper Models não disponível"
else
    echo "ℹ️ Laravel IDE Helper não instalado - instale com: composer require --dev barryvdh/laravel-ide-helper"
fi

echo "✅ Dev Container configurado com sucesso!"

# Testa a configuração do Docker
echo ""
echo "🐳 Testando configuração do Docker..."
if /var/www/html/.devcontainer/test-docker.sh; then
    echo "✅ Docker funcionando corretamente!"
else
    echo "⚠️ Docker com problemas - mas você pode usar os comandos Docker do host"
fi

echo ""
echo "🌐 URLs disponíveis:"
echo "  - Laravel App: http://localhost:8080"
echo "  - Vite Dev Server: http://localhost:5173"
echo "  - MySQL: localhost:3307"
echo ""
echo "🚀 Para iniciar o desenvolvimento:"
echo "  - Laravel: docker-compose up -d"
echo "  - Vite: npm run dev"
echo ""
