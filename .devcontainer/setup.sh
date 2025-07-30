#!/bin/bash

echo "🚀 CONFIGURANDO DEV CONTAINER..."

# Instalar decho "🔧 CORREÇÃO DO COPILOT (se necessário):"
echo "  ./.devcontainer/fix-copilot.sh"
echo ""
echo "🔄 SINCRONIZAR CONFIGURAÇÕES DO COPILOT:"
echo "  ./.devcontainer/sync-copilot-config.sh"
echo ""
echo "🔐 FORÇAR NOVO LOGIN DO COPILOT:"
echo "  ./.devcontainer/force-copilot-login.sh"
echo ""
echo "🎯 SOLUÇÃO DEFINITIVA DO COPILOT:"
echo "  ./.devcontainer/copilot-final-solution.sh"
echo ""
echo "🔍 DIAGNÓSTICO COMPLETO DO COPILOT:"
echo "  ./.devcontainer/diagnose-copilot.sh"
echo ""as npm se necessário
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

# === CONFIGURAR COPILOT PERMISSIONS (CORRIGIDO) ===
echo "🔧 Configurando permissões do GitHub Copilot..."

# Criar diretórios para o usuário www-data (sempre funciona)
mkdir -p /home/www-data/.config/github-copilot 2>/dev/null || true
mkdir -p /home/www-data/.config/Code/User/globalStorage 2>/dev/null || true
mkdir -p /home/www-data/.config/Code/User 2>/dev/null || true

# Tentar criar diretórios root apenas se tivermos permissão
if [ "$(whoami)" = "root" ]; then
    echo "   🔑 Configurando como root..."
    mkdir -p /root/.config/github-copilot 2>/dev/null || true
    mkdir -p /root/.config/Code/User/globalStorage 2>/dev/null || true
    mkdir -p /root/.config/Code/User 2>/dev/null || true
    chown -R root:root /root/.config/ 2>/dev/null || true
else
    echo "   👤 Configurando como www-data (sem permissões root)"
    # Usar sudo apenas se disponível
    sudo mkdir -p /root/.config/github-copilot 2>/dev/null || echo "   ⚠️ Não foi possível criar diretórios root (normal em alguns containers)"
    sudo mkdir -p /root/.config/Code/User/globalStorage 2>/dev/null || true
    sudo mkdir -p /root/.config/Code/User 2>/dev/null || true
fi

# Ajustar permissões do usuário atual
chown -R www-data:www-data /home/www-data/.config/ 2>/dev/null || true
chmod -R 755 /home/www-data/.config/ 2>/dev/null || true

echo "✅ Copilot configurado!"
echo ""
echo "🎯 COMANDOS DISPONÍVEIS:"
echo "  npm run dev          - Iniciar Vite dev server"
echo "  npm run build        - Build para produção"
echo "  npm run format       - Formatar código com Prettier"
echo "  Ctrl+S               - Formatar PHP com Intelephense (automático)"
echo ""
echo "🧪 TESTAR FORMATADORES:"
echo "  ./.devcontainer/test-formatters-container.sh"
echo ""
echo "🧪 TESTE COMPLETO DA CONFIGURAÇÃO:"
echo "  ./.devcontainer/test-setup-final.sh"
echo ""
echo "🔧 CORREÇÃO DO COPILOT (se necessário):"
echo "  ./.devcontainer/fix-copilot.sh"
echo ""
echo "� FORÇAR NOVO LOGIN DO COPILOT:"
echo "  ./.devcontainer/force-copilot-login.sh"
echo ""
echo "�🔍 DIAGNÓSTICO COMPLETO DO COPILOT:"
echo "  ./.devcontainer/diagnose-copilot.sh"
echo ""
