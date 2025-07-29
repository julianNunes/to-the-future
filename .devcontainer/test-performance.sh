#!/bin/bash

# Script para testar performance em tempo real do VS Code
echo "🎯 Teste de Performance VS Code HOST"
echo "===================================="

# Função para testar scroll
test_scroll() {
    echo ""
    echo "🐭 TESTE DE SCROLL:"
    echo "1. Abra um arquivo grande (ex: composer.lock)"
    echo "2. Use o scroll do mouse rapidamente"
    echo "3. Observe se há 'saltos' ou comportamento errático"
    echo ""
    read -p "Pressione ENTER quando terminar o teste de scroll..."
}

# Função para testar digitação
test_typing() {
    echo ""
    echo "⌨️ TESTE DE DIGITAÇÃO:"
    echo "1. Abra um arquivo .php"
    echo "2. Digite rapidamente algumas linhas"
    echo "3. Observe se há delay ou flickering"
    echo ""
    read -p "Pressione ENTER quando terminar o teste de digitação..."
}

# Função para monitorar recursos
monitor_resources() {
    echo ""
    echo "📊 MONITORAMENTO DE RECURSOS (10 segundos):"
    echo "Observando uso de CPU e RAM..."

    for i in {1..10}; do
        # CPU e RAM do VS Code
        CODE_PROCESSES=$(ps aux | grep -E "(code|electron)" | grep -v grep)
        if [ ! -z "$CODE_PROCESSES" ]; then
            CPU_USAGE=$(echo "$CODE_PROCESSES" | awk '{sum += $3} END {print sum}')
            MEM_USAGE=$(echo "$CODE_PROCESSES" | awk '{sum += $4} END {print sum}')
            echo "Segundo $i: CPU: ${CPU_USAGE:-0}% | RAM: ${MEM_USAGE:-0}%"
        else
            echo "Segundo $i: VS Code não encontrado"
        fi
        sleep 1
    done
}

# Menu principal
echo ""
echo "Escolha o teste:"
echo "1) Teste de Scroll"
echo "2) Teste de Digitação"
echo "3) Monitor de Recursos"
echo "4) Todos os testes"
echo "5) Sair"
echo ""
read -p "Digite sua escolha (1-5): " choice

case $choice in
    1)
        test_scroll
        ;;
    2)
        test_typing
        ;;
    3)
        monitor_resources
        ;;
    4)
        test_scroll
        test_typing
        monitor_resources
        ;;
    5)
        echo "Saindo..."
        exit 0
        ;;
    *)
        echo "Opção inválida"
        exit 1
        ;;
esac

echo ""
echo "✅ Teste concluído!"
echo ""
echo "🔍 Se ainda houver problemas:"
echo "1. Execute: ./.devcontainer/fix-host-flickering.sh"
echo "2. Considere usar: code --disable-gpu"
echo "3. Verifique se está no X11 (não Wayland)"
echo ""
