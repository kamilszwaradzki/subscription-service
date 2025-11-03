FROM php:8.3-fpm

# Instalacja wymaganych zależności i narzędzi do kompilacji rozszerzeń
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    zip \
    libicu-dev \
    build-essential \
    && docker-php-ext-configure intl \
    && docker-php-ext-install zip pdo pdo_mysql intl \
    && git clone https://github.com/phpredis/phpredis.git /usr/src/php/ext/redis \
    && docker-php-ext-install redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear

# Instalacja Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Skopiowanie plików aplikacji do kontenera
COPY . /var/www
