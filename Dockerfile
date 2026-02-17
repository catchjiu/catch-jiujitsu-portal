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
        curl \
        git \
        unzip \
        libzip-dev \
        libpq-dev \
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
        pdo_pgsql \
        pdo_sqlite \
        pgsql \
        xml \
        zip \
    && a2enmod rewrite \
    && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf \
    && printf "Listen 80\nListen 3000\nListen 8080\n" > /etc/apache2/ports.conf \
    && sed -ri -e "s!<VirtualHost \\*:80>!<VirtualHost *:80 *:3000 *:8080>!g" /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /app /var/www/html

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && echo "ok" > public/healthz \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

RUN printf '%s\n' \
  '#!/bin/sh' \
  'set -eu' \
  'cd /var/www/html' \
  '' \
  'if [ ! -f .env ]; then' \
  '  cp .env.example .env' \
  'fi' \
  '' \
  'set_env_if_missing() {' \
  '  key="$1"' \
  '  value="$2"' \
  '  env_value="$(printenv "${key}" 2>/dev/null || true)"' \
  '  if [ -z "${env_value}" ]; then' \
  '    if grep -q "^${key}=" .env; then' \
  '      sed -i "s|^${key}=.*|${key}=${value}|" .env' \
  '    else' \
  '      printf "\n%s=%s\n" "${key}" "${value}" >> .env' \
  '    fi' \
  '  fi' \
  '}' \
  '' \
  'set_env_if_missing APP_ENV production' \
  'set_env_if_missing APP_DEBUG false' \
  'set_env_if_missing SESSION_DRIVER file' \
  'set_env_if_missing CACHE_STORE file' \
  'set_env_if_missing QUEUE_CONNECTION sync' \
  '' \
  'current_app_key="${APP_KEY:-$(awk -F= '\''$1=="APP_KEY"{print $2}'\'' .env | tr -d "\r")}"' \
  'if [ -z "${current_app_key}" ]; then' \
  '  php artisan key:generate --force --no-interaction' \
  'fi' \
  '' \
  'mkdir -p database' \
  'touch database/database.sqlite' \
  'chown -R www-data:www-data storage bootstrap/cache database' \
  '' \
  'exec apache2-foreground' \
  > /usr/local/bin/start-container.sh \
  && chmod +x /usr/local/bin/start-container.sh

EXPOSE 80 3000 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
  CMD curl -fsS http://127.0.0.1/healthz || exit 1

CMD ["/usr/local/bin/start-container.sh"]
