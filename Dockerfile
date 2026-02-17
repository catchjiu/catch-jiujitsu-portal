# Catch Jiu Jitsu Portal - Coolify deployment
# React frontend (Vite) + Laravel backend

# ========== Stage 1: Build React frontend ==========
FROM node:20-alpine AS frontend

WORKDIR /app

# Copy root package files and install dependencies
COPY package.json package-lock.json ./
RUN npm ci

# Copy frontend source
COPY . .

# Build React portal (outputs to /app/dist)
# VITE_API_URL empty = same-origin API; VITE_BASE_PATH=/portal/
ENV VITE_API_URL=
ENV VITE_BASE_PATH=/portal/
RUN npm run build -- --config vite.config.ts

# ========== Stage 2: Composer dependencies ==========
FROM composer:2 AS composer

WORKDIR /app

# Copy Composer files first for cache efficiency
COPY composer.json composer.lock ./

# Install PHP dependencies (no dev for production)
RUN composer install \
    --no-dev \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist \
    --no-interaction

# Copy full application source (excluding vendor via .dockerignore)
COPY . .

# Copy built portal to Laravel public directory
RUN mkdir -p public/portal
COPY --from=frontend /app/dist/ ./public/portal/

# ========== Stage 3: Production image ==========
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    libxml2-dev \
    linux-headers \
    oniguruma-dev

# Install PHP extensions (Laravel + Intervention Image)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        xml

# PHP config for Laravel
RUN echo "memory_limit=256M" > /usr/local/etc/php/conf.d/memory.ini \
    && echo "upload_max_filesize=30M" >> /usr/local/etc/php/conf.d/memory.ini \
    && echo "post_max_size=35M" >> /usr/local/etc/php/conf.d/memory.ini

WORKDIR /var/www/html

# Copy Laravel app from composer stage
COPY --from=composer /app .

# Create required directories and set permissions
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Nginx config
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/http.d/default.conf

# Supervisor (nginx + php-fpm + queue worker)
COPY docker/supervisord.conf /etc/supervisord.conf

# Expose port 80 (Coolify expects this)
EXPOSE 80

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
