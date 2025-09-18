# 🔧 Resolução de Problemas - To The Future

## 🚨 Problemas Mais Comuns

### 1. Containers não iniciam

#### Sintomas

- `./start.sh dev` falha
- Erro "port already in use"
- Containers param imediatamente

#### Diagnóstico

```bash
# Verificar quais portas estão em uso
netstat -tulpn | grep :8080    # Nginx
netstat -tulpn | grep :3306    # MySQL
netstat -tulpn | grep :9003    # Xdebug

# Ver status dos containers
docker compose ps

# Ver logs de erro
docker compose logs
```

#### Soluções

```bash
# Solução 1: Parar processos conflitantes
sudo lsof -i :8000             # Ver qual processo usa a porta
sudo kill -9 PID               # Matar processo específico

# Solução 2: Limpeza completa
./start.sh stop
./start.sh clean
./start.sh dev

# Solução 3: Verificar Docker
sudo systemctl status docker   # Docker está rodando?
sudo systemctl restart docker  # Restart Docker se necessário

# Solução 4: Rebuild containers
docker compose down
docker compose build --no-cache
docker compose up -d
```

### 2. VS Code Extensions não funcionam

#### Sintomas

- IntelliSense não funciona
- PHP CS Fixer não formata
- Tasks não aparecem

#### Diagnóstico

```bash
# Verificar se extensões estão instaladas
code --list-extensions | grep intelephense
code --list-extensions | grep php-cs-fixer

# Verificar workspace
code nova-central-v41.code-workspace

# Verificar scripts
ls -la scripts/
./scripts/php.sh -v
```

#### Soluções

```bash
# Solução 1: Reinstalar extensões
./start.sh install-extensions

# Solução 2: Verificar permissões dos scripts
chmod +x scripts/*.sh

# Solução 3: Verificar containers
./start.sh status

# Solução 4: Recarregar VS Code
# Ctrl+Shift+P -> "Developer: Reload Window"

# Solução 5: Verificar configuração do workspace
grep -A 5 -B 5 "php.executablePath" nova-central-v41.code-workspace
```

### 3. PHP/Composer não funciona via scripts

#### Sintomas

- `./scripts/php.sh` retorna erro
- "Container not running"
- Comandos ficam "travados"

#### Diagnóstico

```bash
# Verificar se containers estão rodando
./start.sh status

# Testar conexão direta
docker compose exec app php -v

# Verificar logs
./start.sh logs
```

#### Soluções

```bash
# Solução 1: Iniciar ambiente
./start.sh dev

# Solução 2: Verificar permissões
chmod +x scripts/*.sh

# Solução 3: Testar acesso direto
docker compose exec app bash
# Dentro do container: php -v, composer --version

# Solução 4: Verificar configuração docker-compose
docker compose config

# Solução 5: Rebuild container app
docker compose up --build app
```

### 4. Laravel não carrega / Erro 500

#### Sintomas

- Página em branco
- Erro 500 Internal Server Error
- "Application key not set"

#### Diagnóstico

```bash
# Verificar logs Laravel
./start.sh logs app

# Verificar logs Nginx
./start.sh logs nginx

# Verificar arquivo .env
cat .env | grep APP_KEY

# Verificar permissões
docker compose exec app ls -la storage/
```

#### Soluções

```bash
# Solução 1: Corrigir permissões
./start.sh fix-permissions

# Solução 2: Gerar APP_KEY
./scripts/artisan.sh key:generate

# Solução 3: Limpar caches
./scripts/artisan.sh cache:clear
./scripts/artisan.sh config:clear
./scripts/artisan.sh view:clear

# Solução 4: Verificar .env
cp .env.example .env           # Se não existir
./scripts/artisan.sh key:generate

# Solução 5: Verificar configuração do banco
./scripts/artisan.sh migrate:status
```

### 5. Banco de dados não conecta

#### Sintomas

- "Connection refused"
- "SQLSTATE[HY000] [2002]"
- Migrations falham

#### Diagnóstico

```bash
# Verificar container MySQL
docker compose ps mysql

# Verificar logs MySQL
./start.sh logs mysql

# Testar conexão
docker compose exec mysql mysql -u user -puser -e "SELECT 1"

# Verificar configuração
cat .env | grep DB_
```

#### Soluções

```bash
# Solução 1: Aguardar inicialização do MySQL
sleep 30
./scripts/artisan.sh migrate:status

# Solução 2: Verificar configuração .env
DB_HOST=mysql              # Nome do container, não localhost
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=user
DB_PASSWORD=user

# Solução 3: Restart MySQL
docker compose restart mysql

# Solução 4: Verificar se MySQL iniciou corretamente
docker compose logs mysql | grep "ready for connections"

# Solução 5: Recrear volume MySQL (APAGA DADOS!)
docker compose down
docker volume rm to-the-future_mysql-data
docker compose up -d
```

## ⚡ Problemas de Performance

### 6. Sistema lento / Alto uso de recursos

#### Sintomas

- Containers consomem muita RAM/CPU
- Docker Desktop lento
- Sistema travando

#### Diagnóstico

```bash
# Verificar uso de recursos
docker stats --no-stream

# Verificar espaço em disco
docker system df

# Verificar processos do sistema
top
htop
```

#### Soluções

```bash
# Solução 1: Limpeza de volumes/imagens não utilizados
docker system prune -f
docker volume prune -f

# Solução 2: Otimizar configuração MySQL
# Editar my.cnf para reduzir innodb_buffer_pool_size

# Solução 3: Limitar recursos Docker
# Docker Desktop -> Settings -> Resources

# Solução 4: Parar containers quando não usar
./start.sh stop

# Solução 5: Usar volumes para cache
# (já configurado no docker-compose.yml)
```

### 7. Build lento / Timeout durante build

#### Sintomas

- `docker compose build` demora muito
- Timeout em downloads
- Falha na instalação de pacotes

#### Soluções

```bash
# Solução 1: Build com mais verbose
docker compose build --progress=plain

# Solução 2: Build sem cache
docker compose build --no-cache

# Solução 3: Verificar conexão internet
ping -c 4 google.com

# Solução 4: Usar proxy/espelho (se necessário)
# Configurar no Dockerfile

# Solução 5: Build individual
docker compose build app
docker compose build mysql
```

## 🧠 IntelliSense / IDE Helper

### IntelliSense não reconhece classes Laravel

**Problema**: VS Code não oferece autocomplete para facades, models, etc.

**Solução**:

```bash
# Gerar todos os arquivos helper
./start.sh ide-helper

# Reiniciar VS Code
# Ctrl+Shift+P → "Developer: Reload Window"
```

### Models não aparecem no autocomplete

**Problema**: Properties e methods dos models não são sugeridos.

**Solução**:

```bash
# Regenerar apenas models
./start.sh ide-models

# Verificar se arquivo foi gerado
ls -la _ide_helper_models.php
```

### Erros na geração do IDE Helper

**Problema**: Models PACSDB causam erros de conexão.

**Solução**: Já configurado para ignorar models problemáticos:

```php
// config/ide-helper.php
'ignored_models' => [
    'App\PACSDB\ContentItem',
    'App\PACSDB\Instance',
    // ... outros models PACSDB
]
```

### Após instalar novo package Laravel

**Problema**: Novas facades não aparecem no autocomplete.

**Solução**:

```bash
# Regenerar facades
./start.sh ide-facades

# Ou tudo
./start.sh ide-helper
```

## 🐛 Debug / Xdebug

### 8. Xdebug não funciona

#### Sintomas

- Breakpoints não param
- VS Code não conecta
- "Waiting for connection"

#### Diagnóstico

```bash
# Verificar se Xdebug está carregado
./scripts/php.sh -m | grep -i xdebug

# Verificar configuração
./scripts/php.sh -i | grep -i xdebug

# Verificar porta
netstat -tulpn | grep :9003
```

#### Soluções

```bash
```bash
# Solução 1: Verificar configuração VS Code
# O workspace nova-central-v41.code-workspace já tem configuração de debug:
{
  "name": "Listen for Xdebug",
  "type": "php",
  "request": "launch",
  "port": 9003,
  "pathMappings": {
    "/var/www": "${workspaceFolder}"
  }
}

# Solução 2: Verificar IP do host
ip route show default | awk '/default/ {print $3}'

# Solução 3: Restart container
docker compose restart app

# Solução 4: Verificar firewall
sudo ufw status
sudo ufw allow 9003
```

### 9. Hot reload / Watch não funciona

#### Sintomas

- `npm run watch` não detecta mudanças
- CSS/JS não atualiza automaticamente

#### Soluções

```bash
# Solução 1: Usar polling
./scripts/npm.sh run watch-poll

# Solução 2: Verificar configuração webpack.mix.js
mix.options({
    hmrOptions: {
        host: 'localhost',
        port: 8080
    }
});

# Solução 3: Usar hot reload
./scripts/npm.sh run hot

# Solução 4: Build manual
./scripts/npm.sh run dev
```

### 10. Problemas com Composer/Dependências

#### Sintomas

- "Class not found"
- Autoload não funciona
- Dependências desatualizadas

#### Soluções

```bash
# Solução 1: Regenerar autoload
./scripts/composer.sh dump-autoload

# Solução 2: Limpar cache Composer
./scripts/composer.sh clear-cache

# Solução 3: Reinstalar dependências
rm -rf vendor/
./scripts/composer.sh install

# Solução 4: Verificar composer.json
./scripts/composer.sh validate

# Solução 5: Update dependências
./scripts/composer.sh update
```

## 🔄 Problemas de Restore

### 11. Restore falha / Timeout

#### Sintomas

- Restore para no meio
- "MySQL server has gone away"
- Timeout durante import

#### Soluções

```bash
# Solução 1: Verificar recursos
free -h                        # Mínimo 8GB RAM
df -h                          # Espaço suficiente

# Solução 2: Aumentar timeouts MySQL
# Editar my-restore.cnf:
wait_timeout = 86400
interactive_timeout = 86400

# Solução 3: Backup menor
# Dividir backup em partes menores

# Solução 4: Usar restore simples
./start.sh simple-restore backup.tar.gz

# Solução 5: Restore manual
docker compose -f docker-compose.restore.yml exec mysql-restore \
  mysql -u root -proot < backup.sql
```

### 12. Backup corrompido

#### Sintomas

- "Invalid tar archive"
- "File not found"
- Erro ao extrair

#### Soluções

```bash
# Solução 1: Verificar integridade
tar -tzf backup.tar.gz > /dev/null && echo "OK" || echo "CORRUPTED"

# Solução 2: Verificar formato
file backup.tar.gz

# Solução 3: Tentar extrair manualmente
tar -xzf backup.tar.gz

# Solução 4: Usar backup alternativo
# Tentar com backup de data diferente

# Solução 5: Reparar arquivo (se possível)
gzip -t backup.tar.gz
```

## 🛠️ Comandos de Diagnóstico Úteis

### Sistema Geral

```bash
# Verificar configuração completa
./start.sh check

# Status de todos os serviços
./start.sh status

# Logs de todos os containers
./start.sh logs

# Informações do Docker
docker system info
docker version

# Recursos do sistema
free -h
df -h
lscpu
```

### Debug do Laravel

```bash
# Informações da aplicação
./scripts/artisan.sh about

# Status do banco
./scripts/artisan.sh migrate:status

# Configuração atual
./scripts/artisan.sh config:show

# Variáveis de ambiente
./scripts/artisan.sh env

# Testar conexão banco
./scripts/artisan.sh tinker
# > DB::connection()->getPdo()
```

### Debug do Docker

```bash
# Inspecionar container
docker compose exec app env
docker compose exec app ps aux

# Logs detalhados
docker compose logs --details app

# Configuração final do compose
docker compose config

# Rede Docker
docker network ls
docker network inspect to-the-future_default
```

## 📞 Quando Pedir Ajuda

### Informações para Incluir

```bash
# 1. Versões
docker --version
docker compose version
uname -a

# 2. Status do ambiente
./start.sh status
./start.sh check

# 3. Logs relevantes
./start.sh logs > logs-completos.txt

# 4. Configuração
cat .env | grep -v PASSWORD
docker compose config

# 5. Recursos do sistema
free -h
df -h
docker stats --no-stream
```

### Template de Reporte

```
**Problema**: [Descrever o problema]

**Passos para reproduzir**:
1. 
2. 
3. 

**Comportamento esperado**: 

**Comportamento atual**: 

**Ambiente**:
- SO: 
- Docker: 
- Comando executado: 

**Logs**:
```

[Cole logs relevantes aqui]

```

**Tentativas já feitas**:
- 
- 
```

## 🎯 Prevenção de Problemas

### Checklist Diário

- [ ] Verificar se containers estão rodando: `./start.sh status`
- [ ] Monitorar uso de recursos: `docker stats --no-stream`
- [ ] Backup regular do banco de desenvolvimento
- [ ] Manter Docker atualizado

### Checklist Semanal

- [ ] Limpeza de containers/volumes: `docker system prune -f`
- [ ] Atualizar dependências: `./scripts/composer.sh update`
- [ ] Verificar logs para erros: `./start.sh logs | grep -i error`
- [ ] Testar restore de backup

### Boas Práticas

1. **Sempre commite com ambiente funcionando**
2. **Use `./start.sh check` antes de começar trabalho**
3. **Mantenha logs limpos** - investigue warnings
4. **Monitore recursos** - Docker pode consumir muito
5. **Documente problemas específicos** do seu ambiente

---

**💡 Lembre-se**: A maioria dos problemas é resolvida com `./start.sh stop` seguido de `./start.sh dev`. Quando em dúvida, reinicie! 🔄
