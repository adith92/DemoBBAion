FROM php:8.3-fpm-alpine

# Install system dependencies + PHP extensions
RUN apk add --no-cache \
    nginx \
    curl \
    zip \
    unzip \
    git \
    sqlite \
    sqlite-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    libxml2-dev \
    openssl-dev \
    gettext \
  && docker-php-ext-install \
    pdo \
    pdo_sqlite \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    xml

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first (layer cache)
COPY composer.json ./

# Install PHP dependencies (no dev, optimized autoloader)
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy full application
COPY . .

# Run post-install scripts
RUN composer run-script post-autoload-dump || true

# Set storage/cache permissions
RUN mkdir -p storage/framework/{sessions,views,cache} \
             storage/logs \
             bootstrap/cache \
             database \
  && touch database/database.sqlite \
  && chmod -R 775 storage bootstrap/cache database \
  && chown -R www-data:www-data storage bootstrap/cache database

# Copy nginx config as template (PORT injected at runtime via envsubst)
COPY docker/nginx.conf /etc/nginx/http.d/default.conf.template
RUN rm -f /etc/nginx/http.d/default.conf

# Copy start script
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]
