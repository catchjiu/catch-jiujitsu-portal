# syntax=docker/dockerfile:1

# ========== Stage 1: Build React frontend ==========
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

# Build static portal files into /app/dist
ENV VITE_API_URL=
ENV VITE_BASE_PATH=/portal/
RUN npm run build -- --config vite.config.ts

# ========== Stage 2: Install Composer dependencies ==========
FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist \
    --no-interaction

COPY . .
COPY --from=frontend /app/dist/ ./public/portal/

# ========== Stage 3: Production runtime (Apache + PHP) ==========
FROM php:8.2-apache AS app

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        libicu-dev \
        libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        pcntl \
        pdo_mysql \
        pdo_sqlite \
        xml \
        zip \
    && a2enmod rewrite \
    && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf \
    && sed -ri -e '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /app /var/www/html

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]
