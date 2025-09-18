#!/bin/bash

# Script principal para gerenciar o ambiente Docker To The Future
# Uso: ./start.sh [comando]

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Verificar se foi passado um comando
if [ $# -eq 0 ]; then
    echo -e "${BLUE}=== TO THE FUTURE - GERENCIADOR DOCKER ===${NC}"
    echo ""
    echo "Uso: ./start.sh [comando]"
    echo ""
    echo -e "${YELLOW}📋 Comandos disponíveis:${NC}"
    echo ""
    echo -e "${GREEN}🚀 Desenvolvimento:${NC}"
    echo "  dev                      - Inicia ambiente de desenvolvimento"
    echo "  dev-full                 - Inicia ambiente com Vite (frontend hot reload)"
    echo "  stop                     - Para todos os ambientes"
    echo "  logs                     - Mostra logs do ambiente ativo"
    echo "  status                   - Status dos containers"
    echo ""
    echo -e "${GREEN}🛠️  Utilitários:${NC}"
    echo "  clean                    - Remove containers e volumes não utilizados"
    echo "  setup                    - Configuração inicial do ambiente para desenvolvedores"
    echo "  install-extensions       - Instala extensões recomendadas do VS Code"
    echo "  check                    - Verifica se o ambiente está configurado corretamente"
    echo "  check-php                - Verifica PHP CS Fixer, Xdebug e outras ferramentas"
    echo "  fix-permissions          - Corrige permissões do Laravel (se necessário)"
    echo "  optimize-dev             - Otimiza ambiente para desenvolvimento"
    echo "  setup-php-wrapper        - Configura wrapper PHP para Intelephense"
    echo "  test-php-wrapper         - Testa se wrapper PHP está funcionando"
    echo "  mount-vendor             - Monta volume vendor do Docker (acesso direto ao Intelephense)"
    echo "  unmount-vendor           - Desmonta volume vendor"
    echo ""
    echo -e "${GREEN}🧠 IntelliSense/IDE Helper:${NC}"
    echo "  ide-helper               - Gera todos os arquivos helper para IntelliSense"
    echo "  ide-models               - Gera helper apenas dos models (sem modificá-los)"
    echo "  ide-facades              - Gera helper das facades do Laravel"
    echo "  ide-meta                 - Gera arquivo meta para PHPStorm"
    echo ""
    echo -e "${YELLOW}💡 Exemplos de uso:${NC}"
    echo "  ./start.sh dev"
    echo "  ./start.sh dev-full      # Com Vite para development do frontend"
    echo ""
    echo -e "${BLUE}📝 Processo para iniciar desenvolvimento:${NC}"
    echo "1. Execute: ./start.sh setup (primeira vez)"
    echo "2. Execute: ./start.sh dev"
    echo "3. Abra: code to-the-future.code-workspace"
    echo "4. Acesse: http://localhost:8080"
    echo ""
    echo -e "${YELLOW}🔌 Conexão DBeaver (após iniciar dev):${NC}"
    echo "Host: localhost | Porta: 3307 | Database: laravel"
    echo "Usuário: admin | Senha: root1234"
    exit 0
fi

COMMAND=$1

case $COMMAND in
    "dev")
        echo -e "${GREEN}🚀 Iniciando ambiente de desenvolvimento...${NC}"
        docker compose down >/dev/null 2>&1
        docker compose up -d php nginx db

        echo -e "${YELLOW}⏳ Aguardando containers iniciarem...${NC}"
        sleep 5

        echo -e "${BLUE}🔧 Configurando permissões automaticamente...${NC}"
        if [ -f "./scripts/fix-permissions.sh" ]; then
            ./scripts/fix-permissions.sh
        fi

        echo -e "${GREEN}✅ Ambiente de desenvolvimento ativo!${NC}"
        echo ""
        echo -e "${BLUE}🌐 Acessos:${NC}"
        echo "• Aplicação Web: http://localhost:8080"
        echo "• MySQL: localhost:3307"
        echo "• Usuário DB: admin | Senha: root1234 | Database: laravel"
        echo ""
        echo -e "${YELLOW}💡 Para DBeaver:${NC}"
        echo "Host: localhost, Porta: 3307, Database: laravel"
        echo "Usuário: admin, Senha: root1234"
        echo ""
        echo "Use './start.sh logs' para ver os logs"
        ;;

    "dev-full")
        echo -e "${GREEN}🚀 Iniciando ambiente completo (com Vite)...${NC}"
        docker compose down >/dev/null 2>&1
        docker compose --profile vite-dev up -d

        echo -e "${YELLOW}⏳ Aguardando containers iniciarem...${NC}"
        sleep 5

        echo -e "${BLUE}� Configurando permissões automaticamente...${NC}"
        if [ -f "./scripts/fix-permissions.sh" ]; then
            ./scripts/fix-permissions.sh
        fi

        echo -e "${GREEN}✅ Ambiente completo ativo!${NC}"
        echo ""
        echo -e "${BLUE}🌐 Acessos:${NC}"
        echo "• Aplicação Web: http://localhost:8080"
        echo "• Vite Dev Server: http://localhost:5173"
        echo "• MySQL: localhost:3307"
        echo "• Usuário DB: admin | Senha: root1234 | Database: laravel"
        echo ""
        echo -e "${YELLOW}� Hot Reload ativo no Vite!${NC}"
        echo "Use './start.sh logs' para ver os logs"
        ;;

    "stop")
        echo -e "${YELLOW}⏹️  Parando todos os ambientes...${NC}"
        docker compose down
        docker compose --profile vite-dev down
        echo -e "${GREEN}✅ Todos os ambientes parados${NC}"
        ;;

    "status")
        echo -e "${BLUE}📊 Status dos containers${NC}"
        echo ""
        echo -e "${YELLOW}Ambiente To The Future:${NC}"
        docker compose ps
        ;;

    "logs")
        echo -e "${BLUE}📋 Logs do ambiente ativo${NC}"
        if docker compose ps | grep -q "Up"; then
            echo -e "${YELLOW}Mostrando logs do ambiente To The Future${NC}"
            docker compose logs -f --tail=50
        else
            echo -e "${RED}⚠️  Nenhum ambiente está rodando${NC}"
        fi
        ;;

    "clean")
        echo -e "${YELLOW}🧹 Limpando containers, volumes e imagens não utilizados...${NC}"
        docker compose down -v
        docker compose --profile vite-dev down -v
        docker system prune -f
        docker volume prune -f
        echo -e "${GREEN}✅ Limpeza concluída${NC}"
        ;;

    "setup")
        echo -e "${BLUE}=== TO THE FUTURE - SETUP DESENVOLVEDOR ===${NC}"
        echo -e "${YELLOW}Configurando ambiente completo para desenvolvimento...${NC}"
        echo ""

        # Verificar pré-requisitos
        echo -e "${YELLOW}🔍 Verificando pré-requisitos...${NC}"

        # Verificar Docker
        if ! command -v docker &> /dev/null; then
            echo -e "${RED}❌ Docker não está instalado${NC}"
            echo "Instale o Docker: https://docs.docker.com/get-docker/"
            exit 1
        fi
        echo -e "${GREEN}✅ Docker encontrado${NC}"

        # Verificar Docker Compose
        if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
            echo -e "${RED}❌ Docker Compose não está instalado${NC}"
            echo "Instale o Docker Compose: https://docs.docker.com/compose/install/"
            exit 1
        fi
        echo -e "${GREEN}✅ Docker Compose encontrado${NC}"

        # Verificar VS Code
        if ! command -v code &> /dev/null; then
            echo -e "${YELLOW}⚠️  VS Code não encontrado${NC}"
            echo "Recomendamos instalar o VS Code para uma melhor experiência"
            echo "Continuando sem instalar extensões..."
        else
            echo -e "${GREEN}✅ VS Code encontrado${NC}"
        fi

        echo ""

        # Dar permissões aos scripts
        echo -e "${YELLOW}🔧 Configurando permissões dos scripts...${NC}"
        chmod +x scripts/*.sh 2>/dev/null || true
        chmod +x start.sh
        echo -e "${GREEN}✅ Permissões configuradas${NC}"

        # Instalar extensões se VS Code estiver disponível
        if command -v code &> /dev/null; then
            echo -e "${YELLOW}🧩 Instalando extensões VS Code...${NC}"
            if [ -f "./scripts/install-vscode-extensions.sh" ]; then
                ./scripts/install-vscode-extensions.sh
            else
                echo -e "${YELLOW}⚠️  Script de extensões não encontrado${NC}"
            fi
        fi

        # Criar arquivo .env se não existir
        if [ ! -f ".env" ]; then
            echo -e "${YELLOW}📝 Criando arquivo .env...${NC}"
            if [ -f ".env.example" ]; then
                cp .env.example .env
                echo -e "${GREEN}✅ Arquivo .env criado com base no .env.example${NC}"
            else
                echo -e "${YELLOW}⚠️  .env.example não encontrado. Configure manualmente.${NC}"
            fi
        fi

        # Tentar iniciar o ambiente
        echo ""
        echo -e "${YELLOW}🚀 Tentando iniciar o ambiente de desenvolvimento...${NC}"
        if ./start.sh dev; then
            echo ""
            echo -e "${GREEN}🎉 Ambiente iniciado com sucesso!${NC}"
            echo ""
            echo -e "${BLUE}🌐 Aplicação disponível em:${NC}"
            echo "• Web: ${GREEN}http://localhost:8080${NC}"
            echo "• MySQL: ${GREEN}localhost:3307${NC}"
            echo ""
            echo -e "${YELLOW}📋 Próximos passos:${NC}"
            echo "1. Abra o VS Code: ${GREEN}code to-the-future.code-workspace${NC}"
            echo "2. Execute migrations: ${GREEN}./scripts/artisan.sh migrate${NC}"
            echo "3. Instale dependências: ${GREEN}./scripts/composer.sh install${NC}"
            echo "4. Build frontend: ${GREEN}./scripts/npm.sh install && ./scripts/npm.sh run dev${NC}"
            echo ""
            echo -e "${BLUE}💡 Dicas:${NC}"
            echo "• Use Ctrl+Shift+P no VS Code para acessar Tasks"
            echo "• Execute './start.sh logs' para monitorar"
            echo "• Execute './start.sh ide-helper' para melhor IntelliSense"

        else
            echo -e "${YELLOW}⚠️  Problema ao iniciar ambiente. Você pode tentar manualmente depois.${NC}"
        fi

        echo ""
        echo -e "${BLUE}=== SETUP CONCLUÍDO ===${NC}"
        echo ""
        echo -e "${GREEN}🎉 Ambiente configurado com sucesso!${NC}"
        echo ""
        echo -e "${YELLOW}📋 Próximos passos:${NC}"
        echo "1. Abra o VS Code: ${GREEN}code to-the-future.code-workspace${NC}"
        echo "2. Reinicie o VS Code para aplicar configurações do Intelephense"
        echo "3. Configure seu arquivo .env se necessário"
        echo "4. Execute migrations: ${GREEN}./scripts/artisan.sh migrate${NC}"
        echo "5. Acesse a aplicação: ${GREEN}http://localhost:8080${NC}"
        echo ""
        echo -e "${YELLOW}📚 Documentação completa: ${GREEN}docs/readme/${NC}"
        ;;

    "install-extensions")
        echo -e "${BLUE}=== INSTALADOR DE EXTENSÕES VS CODE ===${NC}"

        # Verificar se o code está instalado
        if ! command -v code &> /dev/null; then
            echo -e "${RED}❌ VS Code não está instalado ou não está no PATH${NC}"
            exit 1
        fi

        if [ -f "./scripts/install-vscode-extensions.sh" ]; then
            ./scripts/install-vscode-extensions.sh
        else
            echo -e "${RED}❌ Script install-vscode-extensions.sh não encontrado${NC}"
            exit 1
        fi
        ;;

    "check")
        if [ -f "./scripts/check-environment.sh" ]; then
            ./scripts/check-environment.sh
        else
            echo -e "${RED}❌ Script check-environment.sh não encontrado${NC}"
            exit 1
        fi
        ;;

    "check-php")
        if [ -f "./scripts/check-php-tools.sh" ]; then
            ./scripts/check-php-tools.sh
        else
            echo -e "${RED}❌ Script check-php-tools.sh não encontrado${NC}"
            exit 1
        fi
        ;;

    "fix-permissions")
        if [ -f "./scripts/fix-permissions.sh" ]; then
            ./scripts/fix-permissions.sh
        else
            echo -e "${RED}❌ Script fix-permissions.sh não encontrado${NC}"
            exit 1
        fi
        ;;

    "ide-helper")
        echo -e "${BLUE}🧠 Gerando arquivos IDE Helper completos...${NC}"
        echo ""
        echo -e "${YELLOW}1. Gerando helpers básicos...${NC}"
        ./scripts/artisan.sh ide-helper:generate
        echo ""
        echo -e "${YELLOW}2. Gerando helpers dos models (sem modificar arquivos)...${NC}"
        ./scripts/artisan.sh ide-helper:models --nowrite
        echo ""
        echo -e "${YELLOW}3. Gerando meta para PHPStorm...${NC}"
        ./scripts/artisan.sh ide-helper:meta
        echo ""
        echo -e "${GREEN}✅ Todos os arquivos IDE Helper gerados com sucesso!${NC}"
        echo ""
        echo -e "${BLUE}📁 Arquivos gerados:${NC}"
        echo "• _ide_helper.php - Facades e helpers gerais"
        echo "• _ide_helper_models.php - Models sem poluir arquivos originais"
        echo "• .phpstorm.meta.php - Meta informações para PHPStorm"
        echo ""
        echo -e "${YELLOW}💡 Reinicie o VS Code para aplicar as mudanças no IntelliSense${NC}"
        ;;

    "ide-models")
        echo -e "${BLUE}🧠 Gerando helper dos models (sem modificar arquivos)...${NC}"
        ./scripts/artisan.sh ide-helper:models --nowrite
        echo -e "${GREEN}✅ Helper dos models gerado: _ide_helper_models.php${NC}"
        echo -e "${YELLOW}💡 Este arquivo não modifica seus models originais${NC}"
        ;;

    "ide-facades")
        echo -e "${BLUE}🧠 Gerando helper das facades...${NC}"
        ./scripts/artisan.sh ide-helper:generate
        echo -e "${GREEN}✅ Helper das facades gerado: _ide_helper.php${NC}"
        ;;

    "ide-meta")
        echo -e "${BLUE}🧠 Gerando meta para PHPStorm...${NC}"
        ./scripts/artisan.sh ide-helper:meta
        echo -e "${GREEN}✅ Meta gerado: .phpstorm.meta.php${NC}"
        ;;

    "setup-php-wrapper")
        if [ -f "./scripts/setup-php-wrapper.sh" ]; then
            ./scripts/setup-php-wrapper.sh
        else
            echo -e "${RED}❌ Script setup-php-wrapper.sh não encontrado${NC}"
            exit 1
        fi
        ;;

    "test-php-wrapper")
        echo -e "${BLUE}🔧 Testando PHP wrapper...${NC}"
        if [ -f "./scripts/php-wrapper.sh" ]; then
            echo -e "${YELLOW}Testando ./scripts/php-wrapper.sh --version${NC}"
            ./scripts/php-wrapper.sh --version
        else
            echo -e "${RED}❌ PHP wrapper não encontrado${NC}"
            exit 1
        fi
        ;;

    "mount-vendor")
        if [ -f "./scripts/mount-vendor.sh" ]; then
            ./scripts/mount-vendor.sh
        else
            echo -e "${RED}❌ Script mount-vendor.sh não encontrado${NC}"
            exit 1
        fi
        ;;

    "unmount-vendor")
        echo -e "${YELLOW}� Desmontando volume vendor...${NC}"
        sudo umount ./vendor 2>/dev/null || true
        echo -e "${GREEN}✅ Volume vendor desmontado${NC}"
        ;;

    "optimize-dev")
        if [ -f "./scripts/optimize-dev.sh" ]; then
            ./scripts/optimize-dev.sh
        else
            echo -e "${RED}❌ Script optimize-dev.sh não encontrado${NC}"
            exit 1
        fi
        ;;

    *)
        echo -e "${RED}❌ Comando '$COMMAND' não reconhecido${NC}"
        echo "Execute './start.sh' para ver os comandos disponíveis"
        exit 1
        ;;
esac
