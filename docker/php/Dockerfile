FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    curl \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mysqli \
        zip \
        intl \
        mbstring

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
