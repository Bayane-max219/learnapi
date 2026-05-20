FROM php:8.3-cli-alpine AS base

RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    icu-dev \
    openssl \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath intl opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/learnapi

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

COPY . .

RUN composer dump-autoload --optimize --no-dev

RUN mkdir -p config/jwt \
    && openssl genpkey -algorithm RSA -out config/jwt/private.pem -pkeyopt rsa_keygen_bits:4096 \
    && openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

RUN php bin/console cache:warmup --env=prod 2>/dev/null || true

EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
