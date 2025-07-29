# 📁 Dev Container Files - To The Future

## 📋 Arquivos Ativos

### **Configuração Principal**
- `devcontainer.json` - Configuração principal do Dev Container
- `tasks.json` - Tarefas VS Code automatizadas

### **Scripts Utilitários**
- `setup-optimized.sh` - Script de inicialização otimizado
- `clean.sh` - Script de limpeza completa
- `test-docker.sh` - Teste de configuração Docker

### **Documentação**
- `README.md` - Guia principal do Dev Container
- `QUICK_START.md` - Início rápido
- `TROUBLESHOOTING.md` - Soluções para problemas
- `OTIMIZACOES.md` - Detalhes das otimizações implementadas

## 🚀 Como Usar

1. **Primeira vez:** Abra no VS Code e "Reopen in Container"
2. **Problemas:** Execute `./clean.sh` e rebuild
3. **Dúvidas:** Consulte `TROUBLESHOOTING.md`

## 🧹 Arquivos Removidos (Limpeza)

- ❌ `cleanup.sh` - Duplicado
- ❌ `performance.md` - Redundante
- ❌ `setup.sh` - Versão não otimizada
- ❌ `.env.performance` - Integrado ao devcontainer.json
- ❌ `settings.json` - Redundante

**Total: 5 arquivos removidos para simplificar o ambiente**

## 🛠️ Execução de Limpeza Manual

Para usuários que desejam executar uma limpeza manual mais profunda:

```bash
cd /home/juliannunes/Projetos/to-the-future
./.devcontainer/clean.sh
```

> **Nota:** Este passo é opcional e geralmente não é necessário. Use apenas se você souber o que está fazendo.
