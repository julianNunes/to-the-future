# 🔄 Guia de Rebuild do Dev Container

## 📋 Checklist Pré-Rebuild

✅ **Arquivos otimizados:**
- devcontainer.json - Configuração principal otimizada
- docker-compose.yml - Network bridge + recursos limitados
- .vscode/settings.json - Formatação otimizada
- .vscode/settings-host.json - Configurações do host

✅ **Arquivos removidos:**
- cleanup.sh, performance.md, setup.sh, .env.performance, settings.json

## 🚀 Passos para Rebuild

### **1. Feche o VS Code Completamente**
```bash
# No terminal do host:
killall "Visual Studio Code" 2>/dev/null || killall code 2>/dev/null
```

### **2. Limpe o Ambiente (Opcional mas Recomendado)**
```bash
cd /home/juliannunes/Projetos/to-the-future
./.devcontainer/clean.sh
```

### **3. Abra o VS Code**
```bash
code .
```

### **4. Rebuild do Dev Container**
No VS Code:
```
Ctrl+Shift+P
> "Dev Containers: Rebuild Container"
```

**OU** se quiser forçar rebuild completo:
```
Ctrl+Shift+P
> "Dev Containers: Rebuild Container Without Cache"
```

## ⏱️ O que Esperar

### **Durante o Rebuild (~3-5 minutos):**
1. 🏗️ Building containers (Docker)
2. 📦 Installing PHP dependencies (Composer)
3. 📦 Installing Node.js dependencies (NPM)
4. ⚙️ Setting up environment (.env, permissions)
5. 🧹 Clearing caches
6. ✅ Testing database connection

### **Sinais de Sucesso:**
- ✅ Nenhum erro vermelho no terminal de build
- ✅ VS Code carrega com as extensões otimizadas
- ✅ Terminal integrado funciona
- ✅ Portas 8080, 5173, 3307 disponíveis

## 🔍 Verificação Pós-Rebuild

### **1. Teste Básico de Funcionamento:**
```bash
# No terminal integrado do VS Code (dentro do container):
php artisan --version
npm --version
docker --version
```

### **2. Teste de Conectividade:**
```bash
# Testar Laravel
curl -s http://localhost:8080 && echo "✅ Laravel OK"

# Testar Vite
curl -s http://localhost:5173 && echo "✅ Vite OK"

# Testar MySQL
nc -zv 127.0.0.1 3307 && echo "✅ MySQL OK"
```

### **3. Verificar Performance:**
```bash
# Monitor de recursos
docker stats --no-stream

# Se PHP container estiver < 80% CPU/RAM = ✅ Sucesso
```

### **4. Teste de Desenvolvimento:**
```bash
# Terminal 1: Iniciar Vite
npm run dev

# Terminal 2: Verificar se hot-reload funciona
# Edite qualquer arquivo .vue e veja se recarrega automaticamente
```

## 🆘 Possíveis Problemas

### **❌ "Build failed" ou erros Docker:**
```bash
# Limpe tudo e tente novamente:
docker system prune -a -f
./.devcontainer/clean.sh
# Rebuild novamente
```

### **❌ "Extension Host crashed":**
- ✅ Normal na primeira inicialização
- Se persistir > 3x, consulte `TROUBLESHOOTING.md`

### **❌ Portas em conflito:**
```bash
# Verifique se as portas estão livres:
netstat -tlnp | grep -E ":(8080|5173|3307|9000)"
# Pare processos que estejam usando essas portas
```

## ✅ Checklist Final

Após o rebuild, você deve ter:

- [ ] VS Code carregou sem erros
- [ ] Apenas 7 extensões instaladas (não 15+)
- [ ] Terminal integrado funcionando
- [ ] Hot-reload do Vite funcionando
- [ ] Sem "flickering" ao digitar
- [ ] Laravel acessível em http://localhost:8080
- [ ] Banco MySQL conectando na porta 3307

## 🎯 Próximos Passos

Se tudo funcionou:
1. 🎉 **Parabéns!** Ambiente otimizado configurado
2. 📝 **Desenvolva normalmente** - performance melhorada
3. 🔍 **Monitor ocasional** com `docker stats`

Se houver problemas:
1. 📖 **Consulte** `TROUBLESHOOTING.md`
2. 🧹 **Execute** `./.devcontainer/clean.sh`
3. 💬 **Reporte** o problema específico

---

**⚡ Ambiente otimizado para máxima produtividade!**
