FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-interaction --prefer-dist

FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
        git curl unzip libzip-dev libpng-dev libonig-dev libxml2-dev libgmp-dev \
    && docker-php-ext-install \
        pdo_mysql mbstring zip gd xml bcmath gmp \
    && pecl install xdebug-3.3.1 \
    && docker-php-ext-enable xdebug \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y --no-install-recommends dos2unix \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

COPY --from=vendor /app/vendor /var/www/html/vendor

COPY . /var/www/html

RUN find /var/www/html \
        -path /var/www/html/vendor -prune -o \
        -type f \( -name '*.sh' -o -name '*.conf' \) -print0 \
        | xargs -0 dos2unix -q

RUN mkdir -p /var/www/html/runtime /var/www/html/web/assets \
    && chown -R www-data:www-data /var/www/html/runtime /var/www/html/web/assets

WORKDIR /var/www/html