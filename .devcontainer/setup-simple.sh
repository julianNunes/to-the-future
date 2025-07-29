#!/bin/bash

# Script de inicialização equilibrado do Dev Container
echo "🚀 Configuração do Dev Container - Laravel+Inertia+Vue3..."

# Configuração básica de permissões
echo "🔧 Configurando permissões..."
sudo chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
sudo chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Configurar Copilot
echo "🤖 Configurando GitHub Copilot..."
mkdir -p /home/www-data/.config/github-copilot 2>/dev/null || true
mkdir -p /home/www-data/.vscode 2>/dev/null || true
mkdir -p /home/www-data/.ssh 2>/dev/null || true
sudo chown -R www-data:www-data /home/www-data/.config 2>/dev/null || true
sudo chown -R www-data:www-data /home/www-data/.vscode 2>/dev/null || true
sudo chown -R www-data:www-data /home/www-data/.ssh 2>/dev/null || true

# Configurar variáveis de ambiente para Copilot
export GITHUB_COPILOT_DISABLE_TELEMETRY=1
echo "export GITHUB_COPILOT_DISABLE_TELEMETRY=1" >> /home/www-data/.bashrc || true

# Verificar dependências essenciais
if [ ! -f "vendor/autoload.php" ]; then
    echo "📦 Instalando dependências PHP..."
    composer install --no-interaction --optimize-autoloader
fi

if [ ! -d "node_modules" ]; then
    echo "📦 Instalando dependências Node.js..."
    npm ci --silent
fi

# Configuração Laravel básica
if [ ! -f ".env" ]; then
    cp .env.example .env 2>/dev/null || true
fi

# Otimizações Laravel
echo "🧹 Configurando Laravel..."
php artisan config:cache --quiet 2>/dev/null || true
php artisan storage:link --quiet 2>/dev/null || true

# Configurar IDE Helper para Intelephense
echo "🔧 Configurando Laravel IDE Helper..."
if [ -f "vendor/autoload.php" ]; then
    # Verificar se ide-helper está instalado
    if composer show barryvdh/laravel-ide-helper >/dev/null 2>&1; then
        echo "📝 Gerando arquivos IDE Helper..."
        php artisan ide-helper:generate --quiet 2>/dev/null || true
        php artisan ide-helper:models --nowrite --quiet 2>/dev/null || true
        php artisan ide-helper:meta --quiet 2>/dev/null || true
    else
        echo "⚠️ IDE Helper não instalado, apenas usando arquivos existentes"
    fi
fi

# Iniciar serviços
echo "🚀 Iniciando serviços..."
/var/www/html/.devcontainer/start-services.sh

echo ""
echo "✅ Dev Container configurado!"
echo ""
echo "🌐 URLs disponíveis:"
echo "  📱 Laravel: http://localhost:8080"
echo "  ⚡ Vite: http://localhost:5173"
echo "  🗄️ MySQL: localhost:3307"
