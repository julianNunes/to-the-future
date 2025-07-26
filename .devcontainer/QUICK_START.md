# 🚀 Quick Start - Dev Container (Otimizado)

## ✅ Verificação Pós-Setup

Após o Dev Container terminar a configuração automática, execute:

### 1. **Verificar Serviços**
```bash
# Ver status dos containers
docker-compose ps

# Verificar se todas as portas estão funcionando
curl -s http://localhost:8080 > /dev/null && echo "✅ Laravel OK" || echo "❌ Laravel FALHOU"
curl -s http://localhost:5173 > /dev/null && echo "✅ Vite OK" || echo "❌ Vite FALHOU"
nc -zv 127.0.0.1 3307 && echo "✅ MySQL OK" || echo "❌ MySQL FALHOU"
```

### 2. **Testar Conexão Laravel**
```bash
# Verificar se .env foi criado
cat .env | grep DB_

# Testar conexão com banco
php artisan tinker
> DB::connection()->getPdo();
> exit
```

### 3. **Iniciar Desenvolvimento**
```bash
# Terminal 1: Iniciar Vite para hot-reload
npm run dev

# Terminal 2: (opcional) Verificar logs
docker-compose logs -f --tail=20
```

### 4. **Acessar Aplicação**
- **Frontend**: http://localhost:5173
- **Backend**: http://localhost:8080
- **MySQL**: localhost:3307 (admin/root1234)

---

## 🔧 Comandos Úteis

### **Laravel**
```bash
php artisan migrate          # Executar migrações
php artisan make:controller  # Criar controller
php artisan make:model       # Criar model
php artisan route:list       # Listar rotas
php artisan tinker           # REPL interativo
```

### **Vue/Vite**
```bash
npm run dev                  # Desenvolvimento com hot-reload
npm run build               # Build para produção
npm run preview             # Preview do build
```

### **Docker - Otimizado**
```bash
docker-compose up -d        # Iniciar todos os serviços
docker-compose down         # Parar todos os serviços
docker-compose logs -f --tail=20  # Ver logs limitados
docker-compose exec php bash # Acessar container PHP
```

### **Copilot**
- `Ctrl + I`: Chat com Copilot
- `Tab`: Aceitar sugestão
- `Ctrl + →`: Aceitar próxima palavra
- `Esc`: Rejeitar sugestão

---

## 🆘 Performance Issues

Se enfrentar lentidão ou problemas:

### **1. Restart Rápido**
```bash
# Recarregar janela VS Code
Ctrl+Shift+P > "Developer: Reload Window"
```

### **2. Limpeza Completa**
```bash
# Execute o script de limpeza
./.devcontainer/clean.sh
docker-compose up -d --build
```

### **3. Monitor de Recursos**
```bash
# Verificar uso de CPU/RAM
docker stats --no-stream
```

### **4. Troubleshooting Detalhado**
Consulte: `.devcontainer/TROUBLESHOOTING.md`

---

**🎉 Ambiente otimizado para máxima performance!**
