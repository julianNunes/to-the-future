# 🚀 Quick Start - Dev Container

## ✅ Verificação Pós-Setup

Após o Dev Container terminar a configuração automática, execute:

### 1. **Verificar Serviços**
```bash
# Ver status dos containers
docker-compose ps

# Verificar se MySQL está rodando na porta 3307
nc -zv 127.0.0.1 3307
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
docker-compose logs -f
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

### **Docker**
```bash
docker-compose up -d        # Iniciar todos os serviços
docker-compose down         # Parar todos os serviços
docker-compose logs -f      # Ver logs em tempo real
docker-compose exec php bash # Acessar container PHP
```

### **Copilot**
- `Ctrl + I`: Chat com Copilot
- `Tab`: Aceitar sugestão
- `Ctrl + →`: Aceitar próxima palavra
- `Esc`: Rejeitar sugestão

---

## 🆘 Troubleshooting

### **Container não inicia**
```bash
# Rebuild containers
docker-compose down
docker-compose up --build -d
```

### **Erro de permissão**
```bash
# Dentro do container
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### **Banco não conecta**
```bash
# Verificar se MySQL está rodando
docker-compose ps
docker-compose logs db

# Recriar banco se necessário
docker-compose down
docker volume rm to-the-future_db_data
docker-compose up -d
```

### **NPM/Composer falha**
```bash
# Limpar caches e reinstalar
rm -rf node_modules vendor
npm install
composer install
```

---

**🎉 Pronto para desenvolver com Copilot + Docker!**
