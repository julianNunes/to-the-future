#!/bin/bash

echo "🚀 CONFIGURANDO DEV CONTAINER..."

# Instalar dependências npm se necessário
if [ ! -d "node_modules" ]; then
    echo "📦 Instalando dependências npm..."
    npm install
fi

# Instalar dependências composer se necessário
if [ ! -d "vendor" ]; then
    echo "📦 Instalando dependências Composer..."
    composer install
fi

# Verificar se .env existe
if [ ! -f ".env" ]; then
    echo "📝 Criando arquivo .env..."
    cp .env.example .env
    php artisan key:generate
fi

echo "✅ Dev Container configurado com sucesso!"
echo ""
echo "🎯 COMANDOS DISPONÍVEIS:"
echo "  npm run dev          - Iniciar Vite dev server"
echo "  npm run build        - Build para produção"
echo "  npm run format       - Formatar código com Prettier"
echo "  ./vendor/bin/pint    - Formatar PHP com Laravel Pint"
echo ""
echo "🧪 TESTAR FORMATADORES:"
echo "  ./.devcontainer/test-formatters-container.sh"
echo ""
