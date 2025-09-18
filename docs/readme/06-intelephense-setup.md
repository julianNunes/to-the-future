# 🧠 Configuração Intelephense + Docker

## 📋 **Resumo**

Este documento explica como configurar o **Intelephense** para funcionar corretamente com PHP rodando em container Docker, resolvendo problemas de "undefined" em funções Laravel.

## ⚡ **Solução Definitiva: Mount Bind**

O **mount bind** é a solução mais eficiente, proporcionando acesso direto ao volume Docker sem cópias ou sincronizações.

### 🚀 **Setup Rápido**

```bash
# 1. Inicie o ambiente de desenvolvimento
./start.sh dev

# 2. Configure o PHP wrapper (uma vez apenas)
./start.sh setup-php-wrapper

# 3. Monte o volume vendor para acesso do Intelephense
./start.sh mount-vendor

# 4. Reinicie o VS Code
```

### 🔧 **Comandos Disponíveis**

```bash
# Configuração inicial do wrapper PHP
./start.sh setup-php-wrapper

# Montar vendor (acesso direto ao volume Docker)
./start.sh mount-vendor

# Desmontar vendor (quando não precisar mais)
./start.sh unmount-vendor

# Testar se o wrapper está funcionando
./start.sh test-php-wrapper
```

## 🎯 **Como Funciona**

### **1. PHP Wrapper**

- **Arquivo**: `scripts/php-wrapper.sh`
- **Função**: Permite que o Intelephense use o PHP do container
- **Auto-detecta**: Projeto e container automaticamente
- **Transparente**: Funciona como se fosse PHP local

### **2. Mount Bind**

- **Arquivo**: `scripts/mount-vendor.sh`
- **Função**: Monta o volume Docker diretamente no host
- **Performance**: Acesso direto, sem cópias
- **Requer**: Sudo (apenas na primeira execução)

### **3. Workspace Configurado**

- **Arquivo**: `nova-central-v41.code-workspace`
- **Include Paths**: Inclui `./vendor` para o Intelephense
- **PHP Path**: Usa o wrapper automaticamente
- **Tasks**: Comandos integrados no VS Code

## 📈 **Vantagens da Solução Mount Bind**

| Aspecto | Mount Bind | Cópia | Symlink |
|---------|------------|-------|---------|
| **Performance** | ⚡ Acesso direto | 🐌 Lento | ⚡ Rápido |
| **Sincronização** | ✅ Automática | ❌ Manual | ✅ Automática |
| **Espaço em Disco** | ✅ Zero extra | ❌ Duplica tudo | ✅ Zero extra |
| **Compatibilidade** | ✅ Universal | ✅ Universal | ⚠️ Limitada |
| **Setup** | 🔧 Uma vez | 🔄 Sempre | 🔧 Uma vez |

## 🔍 **Verificação**

### **Testar PHP Wrapper**

```bash
./start.sh test-php-wrapper
```

### **Verificar Mount**

```bash
# Deve mostrar que está montado
mountpoint ./vendor

# Deve listar os pacotes
ls ./vendor | head -10
```

### **Status no VS Code**

1. Abra qualquer arquivo PHP do projeto
2. Vá em `App\User` - deve reconhecer a classe
3. Use `Illuminate\Support\Facades\DB` - não deve mostrar "undefined"

## 🛠️ **Troubleshooting**

### **Vendor não aparece no Intelephense**

```bash
# Verifique se está montado
./start.sh mount-vendor

# Reinicie o VS Code
# Ctrl+Shift+P -> "Developer: Reload Window"
```

### **Permission denied no sudo**

```bash
# Configure sudo sem senha para mount (opcional)
sudo visudo
# Adicione: seu_usuario ALL=(ALL) NOPASSWD: /bin/mount, /bin/umount
```

### **Container não está rodando**

```bash
# Inicie o ambiente
./start.sh dev

# Verifique o status
docker compose ps
```

### **PHP wrapper não funciona**

```bash
# Reconfigure o wrapper
./start.sh setup-php-wrapper

# Teste novamente
./start.sh test-php-wrapper
```

## 📂 **Estrutura dos Scripts**

```
scripts/
├── php-wrapper.sh          # Wrapper para PHP do container
├── mount-vendor.sh         # Monta volume vendor
└── unmount-vendor.sh       # Desmonta volume vendor
```

## 🔄 **Workflow Diário**

### **Iniciar Desenvolvimento**

```bash
./start.sh dev              # Inicia containers
./start.sh mount-vendor     # Monta vendor (se necessário)
code nova-central-v41.code-workspace
```

### **Finalizar Desenvolvimento**

```bash
./start.sh unmount-vendor   # Opcional: desmonta vendor
./start.sh stop             # Para containers
```

## ✅ **Checklist de Verificação**

- [ ] Container `novacentral-app` rodando
- [ ] PHP wrapper configurado (`./start.sh setup-php-wrapper`)
- [ ] Volume vendor montado (`./start.sh mount-vendor`)
- [ ] VS Code aberto com o workspace
- [ ] Intelephense reconhecendo classes Laravel
- [ ] Sem erros "undefined" em `Illuminate\*`

## 🎨 **Personalização**

### **Alterar Caminho do Vendor**

Se precisar usar outro caminho, edite `nova-central-v41.code-workspace`:

```jsonc
"intelephense.environment.includePaths": [
    "${workspaceFolder}/seu-caminho-vendor"
]
```

### **Adicionar Mais Stubs**

Para suporte adicional, edite a seção `intelephense.stubs` no workspace.

---

## 🚀 **Resultado Final**

Com esta configuração você terá:

- ✅ **Intelephense completo** com todas as funções Laravel
- ✅ **Performance máxima** sem impacto no desenvolvimento
- ✅ **Zero configuração manual** após setup inicial
- ✅ **Integração total** com VS Code e containers
- ✅ **Workflow simplificado** com comandos únicos
