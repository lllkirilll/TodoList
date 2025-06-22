FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    curl \
    libpq-dev \
    libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip pdo_pgsql \
    && docker-php-ext-install intl \
    && docker-php-ext-enable intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html