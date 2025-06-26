#!/usr/bin/env bash
set -e

echo "[entrypoint-dev] garantindo storage link…"
php artisan storage:link 2>/dev/null || true

echo "[entrypoint-dev] limpando caches…"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "[entrypoint-dev] pronto, subindo php-fpm…"
exec php-fpm
