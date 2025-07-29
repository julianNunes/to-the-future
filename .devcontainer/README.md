842d60064a82:/var/www/html$ .devcontainer/test-devcontainer.sh
🧪 SCRIPT DE TESTE - DEV CONTAINER
==================================

✅ Executando dentro do Dev Container

=== 1. TESTANDO FERRAMENTAS BÁSICAS ===
🔍 PHP:
PHP 8.2.29 (cli) (built: Jul 15 2025 19:23:59) (NTS)
🔍 Node.js:
v22.16.0
🔍 Composer:
PHP version 8.2.29 (/usr/local/bin/php)
Run the "diagnose" command to get more detailed diagnostics output.
Composer version 2.8.10 2025-07-10 19:08:33
🔍 NPM:
11.3.0

=== 2. TESTANDO CONECTIVIDADE ===
🌐 Testando Laravel (8080):
❌ Laravel não está respondendo
🌐 Testando Vite (5173):
❌ Vite não está respondendo
🗄️ Testando MySQL (3307):
❌ MySQL não acessível

=== 3. VERIFICANDO MOUNTS DO COPILOT ===
✅ Diretório github-copilot montado
total 8
drwxr-xr-x    2 www-data www-data      4096 Jul 29 01:48 .
drwxr-sr-x    3 www-data www-data      4096 Jul 29 04:21 ..
✅ Diretório .vscode montado

=== 4. TESTANDO LARAVEL ===
🎯 Testando Artisan:
Laravel Framework 10.48.29
✅ Artisan funcionando

=== 5. ARQUIVOS ESSENCIAIS ===
✅ .env existe
✅ vendor/autoload.php existe
✅ node_modules existe
✅ composer.json existe
✅ package.json existe

🎉 TESTE CONCLUÍDO!

📋 PRÓXIMOS PASSOS:
1. Abrir http://localhost:8080 no navegador
2. Testar GitHub Copilot (Ctrl+I)
3. Executar: php artisan migrate