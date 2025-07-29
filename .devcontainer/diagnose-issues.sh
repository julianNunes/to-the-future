#!/bin/bash

# Script de diagnóstico completo para problemas do Dev Container
echo "🔍 DIAGNÓSTICO COMPLETO - Dev Container"
echo "======================================"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para log
log_error() { echo -e "${RED}❌ $1${NC}"; }
log_success() { echo -e "${GREEN}✅ $1${NC}"; }
log_warning() { echo -e "${YELLOW}⚠️ $1${NC}"; }
log_info() { echo -e "${BLUE}ℹ️ $1${NC}"; }

echo ""
echo "=== 1. DIAGNÓSTICO DE CONTAINERS DOCKER ==="
echo ""

# Verificar se Docker está rodando
if ! docker info &> /dev/null; then
    log_error "Docker não está rodando ou não está acessível"
    exit 1
else
    log_success "Docker está funcionando"
fi

# Status dos containers
echo ""
log_info "Status dos containers:"
docker compose ps

# Verificar containers específicos
echo ""
log_info "Verificando containers individuais:"
CONTAINERS=("to-the-future-php-1" "to-the-future-nginx-1" "to-the-future-vite-1" "to-the-future-db-1")

for container in "${CONTAINERS[@]}"; do
    if docker ps --format "{{.Names}}" | grep -q "$container"; then
        STATUS=$(docker inspect --format='{{.State.Status}}' "$container" 2>/dev/null)
        if [ "$STATUS" = "running" ]; then
            log_success "$container está rodando"
        else
            log_error "$container está parado (Status: $STATUS)"
        fi
    else
        log_error "$container não encontrado"
    fi
done

echo ""
echo "=== 2. VERIFICAÇÃO DE PORTAS ==="
echo ""

# Verificar portas
PORTS=(8080 5173 3307 9000)
for port in "${PORTS[@]}"; do
    if netstat -tulpn 2>/dev/null | grep -q ":$port "; then
        log_success "Porta $port está em uso"
    else
        log_error "Porta $port não está em uso"
    fi
done

echo ""
echo "=== 3. LOGS DOS CONTAINERS ==="
echo ""

# Logs dos containers com problemas
log_info "Últimas linhas dos logs:"
echo ""

echo "--- PHP Container ---"
docker compose logs --tail=10 php 2>/dev/null || log_error "Não foi possível obter logs do PHP"

echo ""
echo "--- Nginx Container ---"
docker compose logs --tail=10 nginx 2>/dev/null || log_error "Não foi possível obter logs do Nginx"

echo ""
echo "--- Vite Container ---"
docker compose logs --tail=10 vite 2>/dev/null || log_error "Não foi possível obter logs do Vite"

echo ""
echo "--- MySQL Container ---"
docker compose logs --tail=10 db 2>/dev/null || log_error "Não foi possível obter logs do MySQL"

echo ""
echo "=== 4. TESTES DE CONECTIVIDADE ==="
echo ""

# Teste de conectividade
log_info "Testando conectividade interna:"

# Teste Laravel
if curl -s --max-time 5 http://localhost:8080 > /dev/null 2>&1; then
    log_success "Laravel (8080) está respondendo"
else
    log_error "Laravel (8080) não está respondendo"
fi

# Teste Vite
if curl -s --max-time 5 http://localhost:5173 > /dev/null 2>&1; then
    log_success "Vite (5173) está respondendo"
else
    log_error "Vite (5173) não está respondendo"
fi

# Teste MySQL
if nc -z 127.0.0.1 3307 2>/dev/null; then
    log_success "MySQL (3307) está acessível"
else
    log_error "MySQL (3307) não está acessível"
fi

echo ""
echo "=== 5. VERIFICAÇÃO DE VOLUMES E MOUNTS ==="
echo ""

# Verificar volumes
log_info "Volumes Docker:"
docker volume ls | grep to-the-future

echo ""
log_info "Verificando mounts no container PHP:"
if docker compose exec -T php mount 2>/dev/null | grep -E "(github-copilot|\.vscode|\.gitconfig)"; then
    log_success "Mounts do Copilot configurados"
else
    log_warning "Mounts do Copilot não encontrados"
fi

echo ""
echo "=== 6. VERIFICAÇÃO DE ARQUIVOS ESSENCIAIS ==="
echo ""

# Verificar arquivos no container
log_info "Verificando arquivos essenciais no container:"
FILES=(".env" "vendor/autoload.php" "node_modules" "composer.json" "package.json")

for file in "${FILES[@]}"; do
    if docker compose exec -T php test -e "/var/www/html/$file" 2>/dev/null; then
        log_success "$file existe"
    else
        log_error "$file não encontrado"
    fi
done

echo ""
echo "=== 7. VERIFICAÇÃO DO COPILOT ==="
echo ""

# Verificar diretórios do Copilot no host
log_info "Verificando diretórios do Copilot no HOST:"
if [ -d "$HOME/.config/github-copilot" ]; then
    log_success "Diretório github-copilot existe no host"
    ls -la "$HOME/.config/github-copilot" | head -5
else
    log_error "Diretório github-copilot não existe no host"
fi

if [ -d "$HOME/.vscode" ]; then
    log_success "Diretório .vscode existe no host"
else
    log_error "Diretório .vscode não existe no host"
fi

# Verificar no container
log_info "Verificando diretórios do Copilot no CONTAINER:"
if docker compose exec -T php test -d "/home/www-data/.config/github-copilot" 2>/dev/null; then
    log_success "Diretório github-copilot montado no container"
else
    log_error "Diretório github-copilot não montado no container"
fi

echo ""
echo "=== 8. RECURSOS DO SISTEMA ==="
echo ""

log_info "Uso de recursos dos containers:"
docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.MemPerc}}" | head -6

echo ""
echo "=== 9. REDE DOCKER ==="
echo ""

log_info "Informações de rede:"
docker network ls | grep to-the-future
echo ""
docker compose exec -T php hostname -I 2>/dev/null | head -1 | tr -d ' \n' && echo " (IP do container PHP)"

echo ""
echo "=== 10. VERIFICAÇÃO DE DEPENDÊNCIAS ==="
echo ""

log_info "Verificando dependências no container:"
if docker compose exec -T php php -v &>/dev/null; then
    log_success "PHP está funcionando"
    docker compose exec -T php php -v | head -1
else
    log_error "PHP não está funcionando"
fi

if docker compose exec -T php node -v &>/dev/null; then
    log_success "Node.js está funcionando"
    docker compose exec -T php node -v
else
    log_error "Node.js não está funcionando"
fi

if docker compose exec -T php composer --version &>/dev/null; then
    log_success "Composer está funcionando"
else
    log_error "Composer não está funcionando"
fi

echo ""
echo "=== RESUMO DO DIAGNÓSTICO ==="
echo ""

# Contadores de problemas
ERRORS=0
WARNINGS=0

# Verificações críticas
if ! docker compose ps | grep -q "Up"; then
    log_error "CRÍTICO: Containers não estão rodando corretamente"
    ((ERRORS++))
fi

if ! curl -s --max-time 5 http://localhost:8080 > /dev/null 2>&1; then
    log_error "CRÍTICO: Laravel não está acessível"
    ((ERRORS++))
fi

if ! curl -s --max-time 5 http://localhost:5173 > /dev/null 2>&1; then
    log_error "CRÍTICO: Vite não está acessível"
    ((ERRORS++))
fi

if ! nc -z 127.0.0.1 3307 2>/dev/null; then
    log_error "CRÍTICO: MySQL não está acessível"
    ((ERRORS++))
fi

echo ""
if [ $ERRORS -eq 0 ]; then
    log_success "✅ SISTEMA FUNCIONANDO CORRETAMENTE"
else
    log_error "❌ ENCONTRADOS $ERRORS PROBLEMAS CRÍTICOS"
    echo ""
    log_info "🔧 SOLUÇÕES RECOMENDADAS:"
    echo ""
    echo "1. Execute o reset completo:"
    echo "   ./.devcontainer/clean.sh"
    echo "   docker compose down"
    echo "   docker compose up -d --build"
    echo ""
    echo "2. Se persistir, reconstrua o Dev Container:"
    echo "   Ctrl+Shift+P > 'Dev Containers: Rebuild Container'"
    echo ""
    echo "3. Verifique os logs detalhados:"
    echo "   docker compose logs -f"
fi

echo ""
log_info "📋 LOGS COMPLETOS SALVOS EM: /tmp/devcontainer-diagnostic-$(date +%Y%m%d-%H%M%S).log"
