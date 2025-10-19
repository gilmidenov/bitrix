FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    libjpeg-dev libpng-dev libwebp-dev libfreetype6-dev zip unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd mysqli pdo_mysql