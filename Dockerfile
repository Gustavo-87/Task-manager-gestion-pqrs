ARG CACHE_BUST=20260718

# Etapa 1: compilar frontend con Vite
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build


# Etapa 2: instalar dependencias PHP
FROM composer:2 AS composer

WORKDIR /app

COPY . .

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist


# Etapa 3: servidor de producción
FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        $PHPIZE_DEPS \
        nginx \
        postgresql-dev \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-install \
        pdo_pgsql \
        intl \
        zip \
        mbstring \
        bcmath

WORKDIR /var/www/html

COPY . .

COPY --from=frontend /app/public/build ./public/build
COPY --from=composer /app/vendor ./vendor

RUN mkdir -p storage/logs bootstrap/cache \
    && touch storage/logs/laravel.log \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY nginx.conf /etc/nginx/nginx.conf.template
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 10000

CMD ["sh", "/usr/local/bin/entrypoint.sh"]
