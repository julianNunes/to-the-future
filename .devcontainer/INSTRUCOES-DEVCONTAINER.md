# 🚀 Guia Rápido - Dev Container Laravel + Inertia + Vue3 + Copilot

## 1. Pré-requisitos
- Docker e Docker Compose instalados
- VS Code com extensão "Dev Containers" instalada
- (Opcional) Extensão GitHub Copilot instalada no VS Code

## 2. Como iniciar o projeto
1. **Clone o repositório:**
   ```bash
   git clone <repo-url>
   cd to-the-future
   ```
2. **Abra a pasta no VS Code**
3. **Abra o Dev Container:**
   - Clique em "Reopen in Container" quando solicitado
   - Ou: `Ctrl+Shift+P` → "Dev Containers: Reopen in Container"
4. **Aguarde a inicialização** (primeira vez pode demorar)

## 3. Scripts úteis
- `setup.sh`         → Inicialização e permissões
- `setup-optimized.sh` → Inicialização otimizada (opcional)
- `setup-simple.sh`  → Inicialização básica (opcional)
- `start-services.sh`→ Sobe serviços Laravel, Vite, etc
- `clean.sh`         → Limpa containers, volumes e dependências

## 4. Configuração do Copilot
- O Copilot roda no Host, mas funciona para arquivos do container
- Basta estar logado no VS Code (Host)
- Sugestões aparecem normalmente em arquivos PHP, JS, Vue, etc
- Se não aparecer, faça login no Copilot no Host e reabra o container

## 5. Formatação de código
- PHP: Intelephense (PSR-12)
- JS/TS/Vue: Prettier
- Configurações em `.devcontainer/settings.json` e `.vscode/settings.json`

## 6. Dicas rápidas
- Para rebuild: `Ctrl+Shift+P` → "Dev Containers: Rebuild Container"
- Para rodar scripts: `./.devcontainer/<nome-do-script>.sh`
- Para limpar tudo: `./.devcontainer/clean.sh`

## 7. Problemas comuns
- **Copilot não sugere:**
  - Verifique login no Host
  - Reabra o container
  - Verifique conexão com internet
- **Permissões:**
  - Rode `setup.sh` para corrigir
- **Serviços não sobem:**
  - Rode `start-services.sh` ou `docker compose up -d --build`

---

> Para dúvidas, consulte este arquivo ou o README principal do projeto.
