FROM php:8.2-apache

# Instalamos dependencias y habilitamos el módulo rewrite
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev libzip-dev zip unzip \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install pdo pdo_mysql gd zip \
    && a2enmod rewrite

# Copiamos la configuración
COPY vhost.conf /etc/apache2/sites-available/000-default.conf
