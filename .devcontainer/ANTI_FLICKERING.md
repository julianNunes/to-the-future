# 🚫 ANTI-FLICKERING - Configurações Implementadas

## ✅ Otimizações Aplicadas para Resolver Flickering

### **1. Settings.json do HOST (Minimalista)**
- ✅ Todas as animações desabilitadas
- ✅ QuickSuggestions desabilitadas
- ✅ Performance máxima para Remote Development
- ✅ Todas as extensões serão carregadas no Dev Container

### **2. DevContainer.json (Anti-Flickering Extremo)**
- ✅ Apenas 4 extensões essenciais
- ✅ Todas as sugestões e hints desabilitados
- ✅ Hover, lightbulb, codeLens todos OFF
- ✅ Intelephense com memória mínima (256MB)
- ✅ File watchers altamente otimizados

### **3. Docker Resources Reduzidos**
- ✅ PHP: 512MB RAM, 0.8 CPU (foi 1GB, 1.0 CPU)
- ✅ Vite: 64MB RAM, 0.1 CPU (foi 128MB, 0.2 CPU)
- ✅ Evita sobrecarga do sistema host

## 🎯 **Checklist Pós-Aplicação**

### **No HOST (Antes de abrir Dev Container):**
```bash
# 1. Feche VS Code completamente
killall "Visual Studio Code" 2>/dev/null || killall code 2>/dev/null

# 2. Limpe o ambiente
cd /home/juliannunes/Projetos/to-the-future
./.devcontainer/clean.sh

# 3. Abra VS Code novamente
code .
```

### **Teste de Performance:**
1. Abra o Dev Container: `Ctrl+Shift+P > Dev Containers: Reopen in Container`
2. Aguarde a configuração automática (2-3 min)
3. **Teste de digitação:** Abra um arquivo PHP e digite rapidamente
4. **Monitor recursos:** `docker stats` em terminal do host

## 🚨 **Se AINDA houver flickering NO HOST:**

### **🔥 EMERGÊNCIA - Diagnóstico Completo:**
```bash
# Execute o script de diagnóstico
./.devcontainer/fix-host-flickering.sh
```

### **🛠️ Soluções Graduais (Teste uma por vez):**

#### **1. Restart Extremo:**
```bash
# Mate todos os processos do VS Code
killall "Visual Studio Code" 2>/dev/null || killall code 2>/dev/null
pkill -f "code" 2>/dev/null || true

# Limpe caches
rm -rf ~/.config/Code/logs/* 2>/dev/null || true
rm -rf ~/.config/Code/User/workspaceStorage/* 2>/dev/null || true

# Abra sem extensões
code --disable-extensions /home/juliannunes/Projetos/to-the-future
```

#### **2. Se for Wayland (Sistema Ubuntu moderno):**
```bash
# Verifique se está usando Wayland
echo $XDG_SESSION_TYPE

# Se retornar "wayland", faça logout e escolha "Ubuntu on Xorg"
# Wayland pode causar problemas de performance com VS Code
```

#### **3. Modo Seguro GPU:**
```bash
# Se tiver GPU NVIDIA/AMD, teste sem aceleração
code --disable-gpu --disable-gpu-compositing /home/juliannunes/Projetos/to-the-future
```

#### **4. Reset Completo das Configurações:**
```bash
# Backup das configurações atuais
mv ~/.config/Code/User/settings.json ~/.config/Code/User/settings.json.backup

# Use apenas configurações mínimas (já criadas pelo script)
# O script fix-host-flickering.sh cria configurações de emergência
```

### **Fallback - Trabalho Híbrido:**
```bash
# Use containers apenas para servidor
docker-compose up -d db nginx vite

# VS Code nativo no host para edição
code . --disable-extensions
```

## 📊 **Resultados Esperados:**

- ⚡ **Startup:** 1-2 min (vs 5+ min antes)
- 🖱️ **Typing lag:** Zero
- � **Mouse scroll:** Suave e responsivo (sem "subir/descer")
- �💾 **RAM usage:** ~600MB total (vs 2GB+ antes)
- 🔄 **Extension crashes:** Zero
- 🎨 **UI responsiveness:** Instantâneo

## 🐭 **PROBLEMA ESPECÍFICO: Scroll "Subindo e Descendo"**

Se o mouse scroll estiver "saltando" ou se comportando erraticamente:

### **Causa Provável:**
- Conflito entre aceleração por hardware e drivers
- Configurações de sensibilidade muito altas
- Wayland interferindo com eventos de mouse
- VS Code tentando processar muitos eventos simultâneos

### **Soluções Específicas:**
```bash
# 1. Teste sensibilidade reduzida (já aplicado no settings.json):
# "editor.mouseWheelScrollSensitivity": 0.5

# 2. Se estiver no Wayland, force X11:
# Logout > Login > Clique na engrenagem > "Ubuntu on Xorg"

# 3. Desabilite aceleração GPU temporariamente:
code --disable-gpu /home/juliannunes/Projetos/to-the-future

# 4. Teste com mouse/touchpad diferente se possível
```

## ⚠️ **Extensões Removidas (Temporariamente):**

- ❌ `GitHub.copilot-chat` - Pode ser pesado
- ❌ `esbenp.prettier-vscode` - Conflito potencial
- ❌ `Vue.volar` - Só se usar Vue intensamente
- ❌ `codingyu.laravel-goto-view` - Extra feature
- ❌ `eamodio.gitlens` - Visual features pesadas

**Você pode reativá-las uma por uma depois que o flickering for resolvido.**

## 🔄 **Rollback se Necessário:**

```bash
# Se algo quebrar, volte para configuração anterior
git checkout HEAD~1 -- .devcontainer/ .vscode/
docker-compose down && docker-compose up -d --build
```

---

**💡 Performance extrema configurada - flickering deve estar resolvido!**
