#!/bin/bash

# Script para montar o volume vendor do Docker no host
# Solução definitiva para acesso do Intelephense ao vendor

echo "🔗 Montando volume vendor do Docker no host..."

# Verificar se o container está rodando
if ! docker compose ps | grep -q "php.*Up"; then
    echo "❌ Container PHP não está rodando."
    echo "Execute: ./start.sh dev"
    exit 1
fi

# Criar diretório vendor se não existir
if [ ! -d "./vendor" ]; then
    mkdir -p ./vendor
fi

# Verificar se já está montado
if mountpoint -q ./vendor; then
    echo "✅ Volume vendor já está montado!"
    echo "📁 Caminho: $(pwd)/vendor"
    echo "🔗 Volume: vendor"

    # Verificar se tem conteúdo acessível
    if [ "$(ls -A ./vendor 2>/dev/null | wc -l)" -gt 0 ]; then
        echo "📦 Vendor acessível: $(ls ./vendor | wc -l) pacotes disponíveis"
        echo ""
        echo "🎯 Intelephense configurado para usar vendor montado!"
        echo "💡 Reinicie o VS Code se necessário"
    else
        echo "⚠️  Volume montado mas sem acesso ao conteúdo"
        echo "Tentando remontar..."
        sudo umount ./vendor 2>/dev/null || true
    fi

    if mountpoint -q ./vendor && [ "$(ls -A ./vendor 2>/dev/null | wc -l)" -gt 0 ]; then
        exit 0
    fi
fi

# Obter o caminho do volume Docker
VOLUME_PATH=$(docker volume inspect to-the-future_vendor --format '{{ .Mountpoint }}' 2>/dev/null)

if [ -z "$VOLUME_PATH" ]; then
    echo "❌ Volume vendor não encontrado!"
    echo "Execute: ./start.sh dev"
    exit 1
fi

echo "📍 Volume encontrado: $VOLUME_PATH"
echo "🔧 Montando volume no diretório local..."

# Montar o volume (requer sudo)
if sudo mount --bind "$VOLUME_PATH" ./vendor; then
    # Verificar se o mount funcionou e tem conteúdo
    if [ "$(ls -A ./vendor 2>/dev/null | wc -l)" -gt 0 ]; then
        echo "✅ Volume vendor montado com sucesso!"
        echo ""
        echo "📋 Informações do mount:"
        echo "• Volume Docker: $VOLUME_PATH"
        echo "• Diretório local: $(pwd)/vendor"
        echo "• Tipo: bind mount"
        echo "• Pacotes disponíveis: $(ls ./vendor | wc -l)"
        echo ""
        echo "🎯 Intelephense agora tem acesso direto ao vendor!"
        echo ""
        echo "🔄 Para desmontar (quando não precisar mais):"
        echo "   ./start.sh unmount-vendor"
        echo ""
        echo "💡 Reinicie o VS Code para aplicar as mudanças"
    else
        echo "⚠️  Volume montado mas sem conteúdo acessível"
        echo "Isso pode ser um problema de permissões do Docker"
        echo "Tentando corrigir permissões..."

        sudo umount ./vendor
        echo "❌ Não foi possível acessar o conteúdo do volume"
        echo "Verifique se o Docker está funcionando corretamente"
        exit 1
    fi
else
    echo "❌ Erro ao montar volume"
    echo "Verifique se você tem permissões sudo"
    exit 1
fi
