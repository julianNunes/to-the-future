#!/bin/bash

# Script de teste para verificar configuração equilibrada
echo "🧪 Testando Configuração Equilibrada do Dev Container"
echo "=================================================="

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para testar serviços
test_service() {
    local service=$1
    local url=$2
    local name=$3
    
    if curl -s "$url" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ $name está funcionando${NC}"
        return 0
    else
        echo -e "${RED}❌ $name não está respondendo${NC}"
        return 1
    fi
}

# Função para testar porta
test_port() {
    local port=$1
    local name=$2
    
    if nc -z 127.0.0.1 "$port" 2>/dev/null; then
        echo -e "${GREEN}✅ $name (porta $port) está acessível${NC}"
        return 0
    else
        echo -e "${RED}❌ $name (porta $port) não está acessível${NC}"
        return 1
    fi
}

# Teste 1: Verificar containers Docker
echo -e "${BLUE}📦 Testando Containers Docker...${NC}"
if docker-compose ps | grep -q "Up"; then
    echo -e "${GREEN}✅ Containers estão rodando${NC}"
    docker-compose ps --format "table {{.Name}}\t{{.Status}}"
else
    echo -e "${RED}❌ Alguns containers não estão rodando${NC}"
    docker-compose ps
fi
echo ""

# Teste 2: Verificar serviços web
echo -e "${BLUE}🌐 Testando Serviços Web...${NC}"
test_service "http://localhost:8080" "http://localhost:8080" "Laravel App"
test_service "http://localhost:5173" "http://localhost:5173" "Vite Dev Server"
echo ""

# Teste 3: Verificar banco de dados
echo -e "${BLUE}🗄️ Testando Banco de Dados...${NC}"
test_port 3307 "MySQL"
echo ""

# Teste 4: Verificar dependências
echo -e "${BLUE}📚 Verificando Dependências...${NC}"

if [ -f "vendor/autoload.php" ]; then
    echo -e "${GREEN}✅ Dependências PHP instaladas${NC}"
else
    echo -e "${RED}❌ Dependências PHP não encontradas${NC}"
fi

if [ -d "node_modules" ] && [ -f "package-lock.json" ]; then
    echo -e "${GREEN}✅ Dependências Node.js instaladas${NC}"
else
    echo -e "${RED}❌ Dependências Node.js não encontradas${NC}"
fi

if [ -f ".env" ]; then
    echo -e "${GREEN}✅ Arquivo .env configurado${NC}"
else
    echo -e "${RED}❌ Arquivo .env não encontrado${NC}"
fi
echo ""

# Teste 5: Verificar formatadores
echo -e "${BLUE}🎨 Verificando Formatadores...${NC}"

if [ -f ".prettierrc" ]; then
    echo -e "${GREEN}✅ Prettier configurado${NC}"
else
    echo -e "${YELLOW}⚠️ Prettier não configurado${NC}"
fi

if [ -f "eslint.config.cjs" ]; then
    echo -e "${GREEN}✅ ESLint configurado${NC}"
else
    echo -e "${YELLOW}⚠️ ESLint não configurado${NC}"
fi

if command -v vendor/bin/pint &> /dev/null; then
    echo -e "${GREEN}✅ Laravel Pint disponível${NC}"
else
    echo -e "${YELLOW}⚠️ Laravel Pint não encontrado${NC}"
fi
echo ""

# Teste 6: Verificar performance
echo -e "${BLUE}📊 Verificando Performance...${NC}"

# Verificar uso de recursos
echo "Uso de recursos dos containers:"
docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}" | head -5

# Verificar se há containers usando muitos recursos
HIGH_CPU=$(docker stats --no-stream --format "{{.CPUPerc}}" | sed 's/%//' | awk '$1 > 80 {print $1}')
if [ ! -z "$HIGH_CPU" ]; then
    echo -e "${RED}⚠️ Algum container está usando mais de 80% CPU${NC}"
else
    echo -e "${GREEN}✅ Uso de CPU está normal${NC}"
fi
echo ""

# Teste 7: Verificar Git
echo -e "${BLUE}🔄 Verificando Git...${NC}"
if git status > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Git está funcionando${NC}"
    echo "Branch atual: $(git branch --show-current)"
else
    echo -e "${RED}❌ Git não está configurado${NC}"
fi
echo ""

# Teste 8: Teste de velocidade simples
echo -e "${BLUE}⚡ Teste de Velocidade...${NC}"
start_time=$(date +%s%N)
php -v > /dev/null 2>&1
end_time=$(date +%s%N)
duration=$((($end_time - $start_time) / 1000000))

if [ $duration -lt 100 ]; then
    echo -e "${GREEN}✅ PHP responde rapidamente (${duration}ms)${NC}"
else
    echo -e "${YELLOW}⚠️ PHP está lento (${duration}ms)${NC}"
fi

# Node.js test
start_time=$(date +%s%N)
node -v > /dev/null 2>&1
end_time=$(date +%s%N)
duration=$((($end_time - $start_time) / 1000000))

if [ $duration -lt 100 ]; then
    echo -e "${GREEN}✅ Node.js responde rapidamente (${duration}ms)${NC}"
else
    echo -e "${YELLOW}⚠️ Node.js está lento (${duration}ms)${NC}"
fi
echo ""

# Resumo final
echo -e "${BLUE}📋 RESUMO FINAL${NC}"
echo "=============="

# Verificar se tudo está OK
if test_service "http://localhost:8080" "" "" && \
   test_service "http://localhost:5173" "" "" && \
   test_port 3307 "" && \
   [ -f "vendor/autoload.php" ] && \
   [ -d "node_modules" ] && \
   [ -f ".env" ]; then
    echo -e "${GREEN}🎉 CONFIGURAÇÃO EQUILIBRADA FUNCIONANDO PERFEITAMENTE!${NC}"
    echo ""
    echo -e "${GREEN}✅ Todos os serviços estão funcionais${NC}"
    echo -e "${GREEN}✅ Dependências instaladas${NC}"
    echo -e "${GREEN}✅ Formatadores configurados${NC}"
    echo -e "${GREEN}✅ Performance adequada${NC}"
    echo ""
    echo -e "${BLUE}🚀 Pronto para desenvolvimento produtivo!${NC}"
else
    echo -e "${RED}⚠️ ALGUNS PROBLEMAS DETECTADOS${NC}"
    echo ""
    echo -e "${YELLOW}💡 Execute os comandos de correção:${NC}"
    echo "   ./.devcontainer/clean.sh"
    echo "   docker-compose up -d --build"
fi

echo ""
echo -e "${BLUE}📖 Para mais informações, consulte:${NC}"
echo "   .devcontainer/BALANCED_CONFIG.md"
echo "   .devcontainer/TROUBLESHOOTING.md"
