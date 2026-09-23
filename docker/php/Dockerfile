FROM php:8.4-fpm

ARG APP_ENV=production

# System dependencies + PHP extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        gnupg \
        ca-certificates \
        git \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        intl \
        zip \
        gd \
        sockets \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Node.js 22 (LTS) for building frontend assets
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Build the application at /srv/app (this directory is copied into the shared
# volume by the entrypoint on first boot, so the mounted volume does not hide it)
WORKDIR /srv/app

COPY composer.json composer.lock ./
COPY package.json package-lock.json ./
RUN composer install --no-dev --no-scripts --no-autoloader \
    && npm ci

COPY . .

RUN composer install --no-dev --no-scripts \
    && php artisan package:discover --ansi \
    && php artisan wayfinder:generate --ansi \
    && npm run build

# Runtime layout
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Slim down the image: frontend build tooling is no longer needed
RUN rm -rf node_modules \
    && apt-get purge -y --auto-remove nodejs || true

WORKDIR /var/www/html
EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]