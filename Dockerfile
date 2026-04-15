FROM php:8.2-fpm

# Install system dependencies + nginx + envsubst
RUN apt-get update && apt-get install -y \
    git curl zip unzip nginx gettext-base \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Raise PHP upload limits
RUN echo "upload_max_filesize=20M\npost_max_size=20M" > /usr/local/etc/php/conf.d/uploads.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy project files
COPY . .

# Create required runtime directories before composer post-autoload hook runs
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www

# Nginx config template (PORT substituted at runtime by start.sh)
COPY docker/nginx.conf.template /etc/nginx/nginx.conf.template

# PHP-FPM pool config — increase worker count so SSE connections don't starve webhooks
COPY docker/php-fpm-www.conf /usr/local/etc/php-fpm.d/www.conf

# Startup script
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]
