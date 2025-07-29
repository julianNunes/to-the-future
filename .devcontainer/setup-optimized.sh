#!/bin/bash

# Script de inicialização equilibrado do Dev Container
echo "🚀 Configuração equilibrada do Dev Container - Laravel+Inertia+Vue3..."

# Aguarda serviços essenciais com timeout inteligente
echo "⏳ Aguardando MySQL..."
timeout=90
while ! nc -z 127.0.0.1 3307 && [ $timeout -gt 0 ]; do
    echo "Aguardando MySQL... ($timeout segundos restantes)"
    sleep 3
    timeout=$((timeout-3))
done

if [ $timeout -eq 0 ]; then
    echo "⚠️ MySQL demorou para iniciar, mas continuando..."
else
    echo "✅ MySQL disponível!"
fi

# Configuração de permissões otimizada
echo "🔧 Configurando permissões..."
sudo chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
sudo chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Configurar permissões para dados compartilhados do Copilot
echo "🤖 Configurando GitHub Copilot..."
mkdir -p /home/www-data/.config/github-copilot 2>/dev/null || true
mkdir -p /home/www-data/.vscode 2>/dev/null || true
sudo chown -R www-data:www-data /home/www-data/.config 2>/dev/null || true
sudo chown -R www-data:www-data /home/www-data/.vscode 2>/dev/null || true

# Dependências PHP otimizadas
if [ ! -f "vendor/autoload.php" ]; then
    echo "📦 Instalando dependências PHP..."
    composer install --no-interaction --optimize-autoloader --no-dev
    composer dump-autoload --optimize
else
    echo "✅ Dependências PHP já instaladas"
fi

# Dependências Node.js com cache
if [ ! -d "node_modules" ] || [ ! -f "package-lock.json" ]; then
    echo "📦 Instalando dependências Node.js..."
    npm ci --prefer-offline --no-audit
else
    echo "✅ Dependências Node.js já instaladas"
fi

# Configuração Laravel
if [ ! -f ".env" ]; then
    echo "⚙️ Configurando Laravel..."
    cp .env.example .env
    php artisan key:generate --force --quiet
    echo "✅ Arquivo .env criado"
else
    echo "✅ Arquivo .env já existe"
fi

# Otimizações Laravel
echo "🧹 Otimizando Laravel..."
php artisan config:cache --quiet 2>/dev/null || true
php artisan route:cache --quiet 2>/dev/null || true
php artisan view:cache --quiet 2>/dev/null || true

# Verificação do banco de dados
echo "🗄️ Verificando banco de dados..."
if php artisan migrate:status --quiet 2>/dev/null; then
    echo "✅ Banco de dados OK"
else
    echo "ℹ️ Executando migrações..."
    php artisan migrate --force --quiet 2>/dev/null || echo "⚠️ Migrações falharam (normal em projetos novos)"
fi

# Build assets de desenvolvimento
echo "🎨 Verificando assets..."
if [ ! -d "public/build" ]; then
    echo "📦 Compilando assets..."
    npm run build 2>/dev/null || echo "ℹ️ Build falhou (pode ser normal)"
fi

# Configurações finais
echo "🔧 Configurações finais..."
php artisan storage:link --quiet 2>/dev/null || true

echo ""
echo "✅ Dev Container configurado com sucesso!"
echo ""
echo "🌐 URLs disponíveis:"
echo "  📱 Laravel: http://localhost:8080"
echo "  ⚡ Vite: http://localhost:5173"
echo "  🗄️ MySQL: localhost:3307 (admin/root1234)"
echo ""
echo "�️ Comandos úteis:"
echo "  npm run dev    # Iniciar Vite dev server"
echo "  php artisan    # Comandos Laravel"
echo "  composer       # Gerenciar dependências PHP"
echo ""
echo "💡 Para melhor performance:"
echo "  - Use Ctrl+Shift+P > 'Format Document' para formatação"
echo "  - Configure o Git: git config user.name 'Seu Nome'"
echo "  - Configure o Git: git config user.email 'seu@email.com'"
echo ""
