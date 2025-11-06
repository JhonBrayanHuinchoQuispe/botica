#!/usr/bin/env bash
set -e

# Directorio del proyecto
APP_DIR="/var/www/html"
cd "$APP_DIR"

echo "[start] Inicializando aplicación Laravel"

# Si por algún motivo vendor no está, instalar
if [ ! -d "vendor" ]; then
  echo "[start] Instalando dependencias con Composer (vendor)"
  composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader || true
fi

# Enlaces y optimizaciones
php artisan storage:link || true
php artisan config:cache || true
php artisan route:clear || true
php artisan view:clear || true
php artisan route:cache || true

# Migraciones (no fallar si DB aún no está lista)
echo "[start] Ejecutando migraciones"
php artisan migrate --force || echo "[start] Migraciones diferidas (DB no disponible)"

echo "[start] Iniciando Apache"
exec apache2-foreground