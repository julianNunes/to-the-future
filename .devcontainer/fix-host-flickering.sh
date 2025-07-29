#!/bin/bash

# Script para diagnosticar e corrigir problemas de flickering no HOST
echo "🔍 Diagnosticando problemas de flickering no HOST..."

# Verifica versão do VS Code
echo "📋 Informações do sistema:"
code --version
echo "OS: $(lsb_release -d 2>/dev/null || echo "$(uname -s) $(uname -r)")"
echo "Memória disponível: $(free -h | grep Mem)"

# Verifica processos do VS Code que podem estar consumindo recursos
echo ""
echo "📊 Processos VS Code rodando:"
ps aux | grep -E "(code|electron)" | grep -v grep | head -10

# Verifica se há extensões rodando no host que não deveriam
echo ""
echo "🔌 Extensões ativas no HOST:"
code --list-extensions --show-versions | head -10

# Limpa cache do VS Code que pode estar corrompido
echo ""
echo "🧹 Limpando caches do VS Code..."
VS_CODE_DIRS=(
    "$HOME/.vscode/extensions"
    "$HOME/.config/Code/User/workspaceStorage"
    "$HOME/.config/Code/logs"
    "$HOME/.config/Code/CachedExtensions"
)

for dir in "${VS_CODE_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        echo "Limpando: $dir"
        # Limpa apenas logs e caches, não as extensões instaladas
        if [[ "$dir" == *"logs"* ]] || [[ "$dir" == *"workspace"* ]] || [[ "$dir" == *"Cache"* ]]; then
            rm -rf "$dir"/* 2>/dev/null || true
        fi
    fi
done

# Verifica GPU e drivers que podem causar problemas de renderização
echo ""
echo "🎮 Informações de GPU:"
if command -v nvidia-smi &> /dev/null; then
    echo "NVIDIA GPU detectada:"
    nvidia-smi --query-gpu=name,memory.total,memory.used --format=csv,noheader
elif command -v lspci &> /dev/null; then
    echo "GPUs disponíveis:"
    lspci | grep -i vga
fi

# Verifica Wayland vs X11 (pode afetar performance)
echo ""
echo "🖥️ Sistema de janelas:"
if [ "$XDG_SESSION_TYPE" = "wayland" ]; then
    echo "Wayland detectado - pode causar problemas de performance"
    echo "💡 Considere usar X11 temporariamente:"
    echo "   Logout > Clique na engrenagem > 'Ubuntu on Xorg'"
else
    echo "X11 detectado - OK para performance"
fi

# Cria configuração de emergência para o VS Code
echo ""
echo "⚙️ Criando configuração de emergência..."
mkdir -p ~/.config/Code/User

cat > ~/.config/Code/User/settings.json << 'EOF'
{
    "workbench.reduceMotion": "on",
    "editor.smoothScrolling": false,
    "editor.cursorBlinking": "solid",
    "editor.minimap.enabled": false,
    "workbench.list.smoothScrolling": false,
    "terminal.integrated.gpuAcceleration": "off",
    "editor.mouseWheelScrollSensitivity": 0.3,
    "workbench.list.fastScrollSensitivity": 3,
    "extensions.autoUpdate": false,
    "telemetry.telemetryLevel": "off"
}
EOF

echo "✅ Configuração de emergência criada em ~/.config/Code/User/settings.json"

# Verifica se há conflitos de porta
echo ""
echo "🌐 Verificando conflitos de porta:"
PORTS=(8080 5173 3307 9000)
for port in "${PORTS[@]}"; do
    if netstat -tlnp 2>/dev/null | grep -q ":$port "; then
        echo "⚠️ Porta $port em uso:"
        netstat -tlnp 2>/dev/null | grep ":$port "
    else
        echo "✅ Porta $port livre"
    fi
done

echo ""
echo "🚀 Próximos passos:"
echo "1. Feche COMPLETAMENTE o VS Code:"
echo "   killall 'Visual Studio Code' 2>/dev/null || killall code"
echo ""
echo "2. Se estiver no Wayland, considere trocar para X11"
echo ""
echo "3. Abra VS Code novamente:"
echo "   code /home/juliannunes/Projetos/to-the-future"
echo ""
echo "4. Se ainda houver problemas, execute:"
echo "   code --disable-gpu --disable-extensions"
echo ""
