FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    postgresql-client \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    curl

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    gd \
    opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json ./
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

COPY . .

# Устанавливаем права
RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]