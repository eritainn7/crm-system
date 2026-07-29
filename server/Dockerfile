FROM php:8.3-fpm-alpine

# Установка зависимостей
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    curl \
    nginx \
    supervisor

# Установка PHP расширений
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    gd \
    opcache

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Рабочая директория
WORKDIR /var/www/html

# Копирование файлов composer.json и composer.lock (если есть)
COPY composer.json composer.lock* ./

# Установка зависимостей (этот слой кэшируется)
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

# Копирование остальных файлов
COPY . .

# Запуск скриптов после копирования всех файлов
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# Установка прав
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Копирование конфигураций
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Порт для PHP-FPM
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php artisan --version || exit 1

# Запуск supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]