#!/bin/sh
set -e

# Default PORT if Railway doesn't set it
export PORT=${PORT:-8080}

cd /var/www

# Laravel bootstrap (optimizations are non-fatal; migrate is required)
php artisan config:cache  || echo "[warn] config:cache failed, continuing..."
php artisan route:cache   || echo "[warn] route:cache failed, continuing..."
php artisan view:cache    || echo "[warn] view:cache failed, continuing..."
php artisan migrate --force
php artisan storage:link --force 2>/dev/null || true

# Substitute PORT into nginx config
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# Start php-fpm in background
php-fpm -D

# Start nginx in foreground (keeps container alive)
exec nginx -g "daemon off;"
