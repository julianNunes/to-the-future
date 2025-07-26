# 🚨 Troubleshooting - Performance Issues

## ⚡ Quick Fixes (Tente primeiro)

### 1. **Reiniciar VS Code Completamente**
```bash
# Feche completamente o VS Code
# No terminal do host:
killall "Visual Studio Code" 2>/dev/null || killall code 2>/dev/null
# Abra novamente
code /home/juliannunes/Projetos/to-the-future
```

### 2. **Limpar Ambiente Dev Container**
```bash
# Execute o script de limpeza
./.devcontainer/clean.sh
```

### 3. **Verificar Recursos do Sistema**
```bash
# Monitor de recursos
docker stats
# Se algum container estiver usando mais de 80% CPU ou RAM, há problema
```

---

## 🔧 Problemas Específicos e Soluções

### **"Remote Extension host terminated unexpectedly"**

**Causa:** Sobrecarga de extensões ou falta de recursos.

**Solução:**
1. Use apenas o mínimo de extensões necessárias (já configurado)
2. Aumente recursos se necessário:
```bash
# Edite docker-compose.yml se precisar de mais recursos
# php service:
#   mem_limit: 3g  # aumente de 2g para 3g
#   cpus: 2.0      # aumente de 1.5 para 2.0
```

### **Lentidão ao Digitar (Flickering)**

**Causa:** Animações e sugestões em tempo real.

**Solução:** Já configurado no devcontainer.json:
- `editor.smoothScrolling: false`
- `editor.cursorBlinking: "solid"`
- `editor.quickSuggestions: off`

### **Extensões Travando**

**Solução:**
```bash
# 1. Recarregue a janela
Ctrl+Shift+P > "Developer: Reload Window"

# 2. Se não resolver, reinstale o Dev Container
Ctrl+Shift+P > "Dev Containers: Rebuild Container"
```

---

## 📊 Monitoramento de Performance

### **Verificar uso de recursos:**
```bash
# CPU e Memória dos containers
docker stats --no-stream

# Processos no container PHP
docker-compose exec php top

# Logs em tempo real
docker-compose logs -f --tail=50
```

### **Verificar se portas estão livres:**
```bash
# Verificar se as portas não estão em conflito
netstat -tlnp | grep -E ":(8080|5173|3307|9000)"
```

---

## 🚀 Performance Extrema (Se ainda estiver lento)

### **1. Desabilitar mais extensões (temporariamente)**
No devcontainer.json, comente extensões não essenciais:
```json
"extensions": [
    "GitHub.copilot",
    // "GitHub.copilot-chat",  // Desabilite temporariamente
    "bmewburn.vscode-intelephense-client",
    // "Vue.volar",  // Desabilite se não estiver trabalhando com Vue
]
```

### **2. Usar VS Code nativo (sem container) temporariamente**
```bash
# Se o problema persistir, trabalhe no host por um tempo
cd /home/juliannunes/Projetos/to-the-future
code .
# Configure PHP, Node.js localmente se necessário
```

### **3. Alternativa: Usar containers individuais**
```bash
# Subir apenas o banco
docker-compose up -d db

# Trabalhar com Laravel localmente
php artisan serve --host=0.0.0.0 --port=8000

# Subir Vite localmente
npm run dev
```

---

## ⚠️ Sinais de Alerta

**Reinicie o Dev Container se:**
- CPU constantemente > 90%
- RAM constantemente > 90%
- Mais de 3 crashes do extension host em 5 min
- Delay de > 2 segundos ao digitar

**Comando para restart completo:**
```bash
./.devcontainer/clean.sh && docker-compose up -d --build
```

---

## 📝 Configurações do Host VS Code

Copie estas configurações para seu `settings.json` pessoal:
```json
{
    "remote.downloadExtensionsLocally": true,
    "extensions.autoUpdate": false,
    "editor.smoothScrolling": false,
    "editor.cursorBlinking": "solid",
    "terminal.integrated.gpuAcceleration": "off"
}
```
