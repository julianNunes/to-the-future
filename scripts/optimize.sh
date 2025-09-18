#!/bin/bash

# =================================================================
# SCRIPT DE OTIMIZAÇÃO - TO THE FUTURE
# =================================================================

echo "🚀 Iniciando otimização da aplicação..."

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 1. Limpar caches
echo -e "${YELLOW}🧹 Limpando caches...${NC}"
docker exec to-the-future-php-1 php artisan cache:clear
docker exec to-the-future-php-1 php artisan config:clear
docker exec to-the-future-php-1 php artisan view:clear
docker exec to-the-future-php-1 php artisan route:clear

# 2. Otimizar para produção
echo -e "${YELLOW}⚡ Gerando caches de produção...${NC}"
docker exec to-the-future-php-1 php artisan config:cache
docker exec to-the-future-php-1 php artisan view:cache

# 3. Compilar assets otimizados
echo -e "${YELLOW}📦 Compilando assets para produção...${NC}"
docker exec to-the-future-php-1 npm run production

# 4. Otimizar autoloader
echo -e "${YELLOW}🔧 Otimizando autoloader...${NC}"
docker exec to-the-future-php-1 composer dump-autoload -o

# 5. Verificar permissões
echo -e "${YELLOW}🔒 Verificando permissões...${NC}"
docker exec to-the-future-php-1 chmod -R 775 storage bootstrap/cache
docker exec to-the-future-php-1 chown -R www-data:www-data storage bootstrap/cache

# 6. Verificar status dos serviços
echo -e "${YELLOW}📊 Status dos serviços:${NC}"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

echo -e "${GREEN}✅ Otimização concluída!${NC}"
echo ""
echo -e "${YELLOW}📋 Próximos passos recomendados:${NC}"
echo "1. Implementar Redis para cache e sessions"
echo "2. Configurar CDN para assets estáticos"
echo "3. Implementar Laravel Octane para performance extrema"
echo "4. Configurar monitoring com Laravel Telescope"
echo ""
echo -e "${GREEN}🌐 Aplicação disponível em: http://localhost:8080${NC}"
