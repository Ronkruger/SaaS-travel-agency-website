#!/bin/sh
set -e

# Default PORT if Railway doesn't set it
export PORT=${PORT:-8080}

cd /var/www

# Laravel bootstrap
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan storage:link --force 2>/dev/null || true

# Substitute PORT into nginx config
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# Start php-fpm in background
php-fpm -D

# Start nginx in foreground (keeps container alive)
exec nginx -g "daemon off;"
