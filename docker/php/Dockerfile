FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libgmp-dev

RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    gd \
    xml \
    bcmath \
    gmp


RUN pecl install xdebug-3.3.1 && docker-php-ext-enable xdebug

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader