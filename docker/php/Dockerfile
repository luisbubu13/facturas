FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    tzdata \
    mariadb-client \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        mysqli \
        pdo \
        pdo_mysql \
        gd \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

ENV TZ=America/Mexico_City

RUN a2enmod rewrite

WORKDIR /var/www/html