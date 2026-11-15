# Используем легкий Alpine-образ для Node.js
FROM node:22-alpine

# Плагин @laravel/vite-plugin-wayfinder при старте dev-сервера вызывает
# `php artisan wayfinder:generate`, поэтому здесь нужен PHP CLI.
RUN apk add --no-cache \
    php84 \
    php84-ctype \
    php84-curl \
    php84-dom \
    php84-fileinfo \
    php84-mbstring \
    php84-openssl \
    php84-pdo \
    php84-pdo_pgsql \
    php84-phar \
    php84-session \
    php84-simplexml \
    php84-tokenizer \
    php84-xml \
    && ln -sf /usr/bin/php84 /usr/local/bin/php

WORKDIR /var/www/html


COPY package.json package-lock.json ./


RUN npm ci

COPY . .


EXPOSE 5173