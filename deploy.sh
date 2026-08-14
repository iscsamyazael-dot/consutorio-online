#!/bin/bash

echo "Iniciando despliegue..."

# 1. Instalar/actualizar dependencias (usando el composer disponible en el servidor)
php composer.phar install --no-dev --optimize-autoloader

# 2. Limpiar caché de Laravel para evitar conflictos
php artisan optimize:clear

# 4. Ajustar permisos para que el servidor web Apache tenga control
chown -R www-data:www-data storage bootstrap/cache

echo "Despliegue completado con éxito."