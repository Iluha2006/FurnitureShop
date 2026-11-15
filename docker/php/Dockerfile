FROM php:8.4-fpm-alpine


# Faster Alpine package mirror for slow networks (dl-cdn is ~2x slower on RU networks).
# Override per build: --build-arg ALPINE_MIRROR=...
ARG ALPINE_MIRROR=https://dl-cdn.alpinelinux.org/alpine

RUN sed -i "s|https\?://dl-cdn.alpinelinux.org/alpine|$ALPINE_MIRROR|" /etc/apk/repositories \
    && apk add --no-cache \
    libpq \
    libzip \
    libpng \
    libjpeg-turbo \
    libwebp \
    freetype \
    icu \
    oniguruma \
    $PHPIZE_DEPS


# Compile-time headers (pkg-config .pc files + includes) needed by docker-php-ext-install.
# Installed in a separate virtual group so they can be removed after compiling,
# keeping only the runtime libs from the layer above.
RUN apk add --no-cache --virtual .php-ext-build-deps \
    zlib-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    libpq-dev \
    linux-headers


RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
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
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .php-ext-build-deps $PHPIZE_DEPS


COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


WORKDIR /var/www/html
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]