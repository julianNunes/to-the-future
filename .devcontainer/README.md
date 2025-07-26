# Dev Container - To The Future (Performance Optimized) 🚀

Este projeto está configurado com um **Dev Container otimizado** para máxima performance e estabilidade.

## ⚡ Features Otimizadas

- ✅ **Extensões mínimas** - Apenas o essencial para evitar sobrecarga
- ✅ **Performance tunning** - Configurações anti-flickering e baixa latência
- ✅ **Recursos limitados** - Evita uso excessivo de CPU/RAM
- ✅ **Network bridged** - Mais estável que modo host
- ✅ **Volumes otimizados** - Cache inteligente para dependencies

## 🏃‍♂️ Quick Start

### 1. **Abrir Dev Container**
```bash
# No VS Code
Ctrl+Shift+P > "Dev Containers: Reopen in Container"
```

### 2. **Verificar Status**
```bash
# Após abrir, verificar se tudo está OK
docker-compose ps
curl -s http://localhost:8080 && echo "✅ Laravel OK"
curl -s http://localhost:5173 && echo "✅ Vite OK"
```

### 3. **Desenvolvimento**
```bash
# Terminal 1: Frontend hot-reload
npm run dev

# Terminal 2: Backend (já está rodando)
php artisan serve # se necessário
```

## � URLs e Portas

| Serviço | URL | Porta |
|---------|-----|-------|
| Laravel | http://localhost:8080 | 8080 |
| Vite | http://localhost:5173 | 5173 |
| MySQL | localhost:3307 | 3307 |

## 🆘 Problemas de Performance?

### Quick Fix
```bash
# 1. Recarregar janela
Ctrl+Shift+P > "Developer: Reload Window"

# 2. Se persistir, limpeza completa
./.devcontainer/clean.sh
docker-compose up -d --build
```

### Troubleshooting Detalhado
📖 Consulte: [TROUBLESHOOTING.md](./TROUBLESHOOTING.md)

## 📊 Monitor de Performance

```bash
# Verificar recursos
docker stats --no-stream

# Se algum container > 80% CPU/RAM = problema
```

## 🎯 Extensões Instaladas (Mínimo Essencial)

- **GitHub Copilot** - IA para código
- **Intelephense** - PHP IntelliSense
- **Laravel Pint** - Formatador PHP
- **Volar** - Vue 3 Support
- **Prettier** - Formatador JS/CSS
- **Remote Containers** - Dev Container

**Nota:** Extensões extras foram removidas para melhor performance.

---

**⚡ Configuração focada em velocidade e estabilidade!**
- **Laravel: Clear Cache** - Limpa cache do Laravel
- **Laravel: Generate IDE Helpers** - Gera arquivos de ajuda do IDE
- **NPM: Install Dependencies** - Instala dependências Node.js
- **Composer: Install Dependencies** - Instala dependências PHP

## 🐳 Comandos Docker úteis

```bash
# Ver logs dos containers
docker-compose logs -f

# Acessar shell do container PHP
docker-compose exec php bash

# Reiniciar serviços
docker-compose restart

# Rebuild containers
docker-compose up --build -d
```

## 🔧 Troubleshooting

### Container não inicia
1. Verifique se o Docker está rodando
2. Verifique se as portas 8080, 5173, 3307 não estão em uso
3. Tente rebuild: `docker-compose up --build -d`

### Problemas de permissão
```bash
# Dentro do container
sudo chown -R www-data:www-data /var/www/html/storage
sudo chown -R www-data:www-data /var/www/html/bootstrap/cache
```

### Dependências não instaladas
```bash
# PHP
composer install

# Node.js
npm install
```

### Banco de dados não conecta
1. Verifique se o MySQL está rodando: `docker-compose ps`
2. Verifique se a porta 3307 está acessível
3. Confira as credenciais no arquivo `.env`

## 📁 Estrutura do Dev Container

```
.devcontainer/
├── devcontainer.json    # Configuração principal
├── settings.json        # Configurações específicas do VS Code
├── tasks.json          # Tarefas automatizadas
├── setup.sh            # Script de inicialização
└── README.md           # Este arquivo
```

## 🔄 Sincronização com o host

Todas as alterações feitas no Dev Container são sincronizadas em tempo real com seu sistema host. Você pode trabalhar normalmente e os arquivos serão mantidos.

## 🎯 Próximos passos

1. Abra o terminal integrado no VS Code
2. Execute `npm run dev` para iniciar o Vite
3. Acesse http://localhost:8080 para ver sua aplicação
4. Comece a programar com o poder do Copilot! 🚀
