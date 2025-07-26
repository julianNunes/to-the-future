#!/bin/bash

# Script de inicialização otimizado do Dev Container
echo "🚀 Configuração otimizada do Dev Container..."

# Aguarda serviços essenciais
echo "⏳ Aguardando MySQL..."
timeout=60
while ! nc -z 127.0.0.1 3307 && [ $timeout -gt 0 ]; do
    echo "Aguardando MySQL... ($timeout segundos restantes)"
    sleep 2
    timeout=$((timeout-2))
done

if [ $timeout -eq 0 ]; then
    echo "⚠️ MySQL demorou para iniciar, mas continuando..."
fi

# Configuração básica de permissões
echo "🔧 Configurando permissões básicas..."
sudo chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
sudo chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Dependências essenciais apenas se necessário
if [ ! -f "vendor/autoload.php" ]; then
    echo "📦 Instalando dependências PHP..."
    composer install --no-interaction --quiet
fi

if [ ! -f "package-lock.json" ] || [ ! -d "node_modules" ]; then
    echo "📦 Instalando dependências Node.js..."
    npm install --silent
fi

# Arquivo .env
if [ ! -f ".env" ]; then
    echo "⚙️ Criando .env..."
    cp .env.example .env
    php artisan key:generate --force --quiet
fi

# Limpeza básica
echo "🧹 Limpeza básica..."
php artisan config:clear --quiet 2>/dev/null || true
php artisan cache:clear --quiet 2>/dev/null || true

# Teste de migração (sem forçar se falhar)
echo "🗄️ Testando banco de dados..."
php artisan migrate --force --quiet 2>/dev/null || echo "ℹ️ Migrações puladas (normal em projetos novos)"

echo "✅ Dev Container otimizado configurado!"
echo ""
echo "🌐 Acesse:"
echo "  - Laravel: http://localhost:8080"
echo "  - Vite: http://localhost:5173"
echo "  - MySQL: localhost:3307"
echo ""
echo "💡 Para problemas de performance, consulte:"
echo "  .devcontainer/TROUBLESHOOTING.md"
echo ""
