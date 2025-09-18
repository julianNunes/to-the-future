#!/bin/bash

# =================================================================
# SCRIPT DE OTIMIZAÇÃO PARA DESENVOLVIMENTO - TO THE FUTURE
# =================================================================

echo "🚀 Otimizando ambiente de DESENVOLVIMENTO..."

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Verificar se container está rodando
if ! docker ps | grep -q "to-the-future-php-1"; then
    echo -e "${RED}❌ Container to-the-future-php-1 não está rodando!${NC}"
    echo "Execute: ./start.sh dev"
    exit 1
fi

echo -e "${BLUE}📋 Configurações para DESENVOLVIMENTO:${NC}"

# 1. Limpar TODOS os caches (necessário para dev)
echo -e "${YELLOW}🧹 Limpando todos os caches (obrigatório para dev)...${NC}"
docker exec to-the-future-php-1 php /var/www/artisan config:clear
docker exec to-the-future-php-1 php /var/www/artisan view:clear
docker exec to-the-future-php-1 php /var/www/artisan route:clear
docker exec to-the-future-php-1 php /var/www/artisan cache:clear 2>/dev/null || echo "Cache clear ignorado (normal em dev)"

# 2. Corrigir permissões para desenvolvimento
echo -e "${YELLOW}🔒 Configurando permissões para desenvolvimento...${NC}"
docker exec to-the-future-php-1 chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker exec to-the-future-php-1 chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# 3. Otimizações específicas para desenvolvimento
echo -e "${YELLOW}⚡ Aplicando otimizações de DESENVOLVIMENTO...${NC}"

# Autoloader otimizado (mantém para performance)
docker exec to-the-future-php-1 composer dump-autoload -o

# 4. Verificar Xdebug (importante para dev)
echo -e "${YELLOW}🔧 Verificando Xdebug...${NC}"
docker exec to-the-future-php-1 php -m | grep -i xdebug > /dev/null && echo "✅ Xdebug ativo" || echo "❌ Xdebug não encontrado"

# 5. Compilar assets para desenvolvimento (modo watch se disponível)
echo -e "${YELLOW}📦 Configurando assets para desenvolvimento...${NC}"
if docker exec to-the-future-php-1 test -f /var/www/package.json; then
    echo "Verificando se npm install foi executado..."
    docker exec to-the-future-php-1 npm install --silent 2>/dev/null || echo "NPM install pode ser necessário"
    echo "Para hot-reload, execute: docker exec to-the-future-php-1 npm run dev"
    echo "Para watch mode, execute: docker exec to-the-future-php-1 npm run watch"
else
    echo "package.json não encontrado, pulando assets"
fi

# 6. Verificar configuração de desenvolvimento
echo -e "${YELLOW}🔍 Verificando configurações de desenvolvimento...${NC}"
APP_ENV=$(docker exec to-the-future-php-1 php -r "echo env('APP_ENV', 'undefined');")
APP_DEBUG=$(docker exec to-the-future-php-1 php -r "echo env('APP_DEBUG', 'undefined');")

echo "APP_ENV: $APP_ENV"
echo "APP_DEBUG: $APP_DEBUG"

if [ "$APP_ENV" != "local" ] && [ "$APP_ENV" != "development" ]; then
    echo -e "${YELLOW}⚠️  Recomendado: APP_ENV=local para desenvolvimento${NC}"
fi

if [ "$APP_DEBUG" != "true" ]; then
    echo -e "${YELLOW}⚠️  Recomendado: APP_DEBUG=true para desenvolvimento${NC}"
fi

# 7. Status final
echo -e "${YELLOW}📊 Status dos serviços:${NC}"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep to-the-future

echo ""
echo -e "${GREEN}✅ Ambiente de desenvolvimento otimizado!${NC}"
echo ""
echo -e "${BLUE}💡 Comandos úteis para desenvolvimento:${NC}"
echo "• Hot reload assets: docker exec to-the-future-php-1 npm run watch"
echo "• Compilar assets: docker exec to-the-future-php-1 npm run dev"
echo "• Ver logs: ./start.sh logs"
echo "• Corrigir permissões: ./start.sh fix-permissions"
echo ""
echo -e "${GREEN}🌐 Aplicação disponível em: http://localhost:8080${NC}"
echo ""
echo -e "${YELLOW}📝 Importante para DEV:${NC}"
echo "• Caches limpos (mudanças aplicadas imediatamente)"
echo "• Xdebug ativo para debugging"
echo "• Permissões configuradas"
echo "• Autoloader otimizado mantido"
