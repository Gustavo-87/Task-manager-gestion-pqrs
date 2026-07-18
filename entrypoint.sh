#!/bin/sh
set -e

cd /var/www/html

echo "Ajustando permisos..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

if [ -n "$DB_URL" ]; then
    echo "Conexión de base de datos detectada."
else
    echo "ADVERTENCIA: DB_URL no está definida."
fi

php artisan optimize:clear
php artisan config:cache
php artisan view:cache

# No se ejecuta route:cache porque routes/web.php contiene una ruta Closure.
php artisan storage:link --force || true

echo "Ejecutando migraciones..."
php artisan migrate --force

PORT="${PORT:-10000}"
sed "s/__PORT__/${PORT}/g"     /etc/nginx/nginx.conf.template     > /etc/nginx/nginx.conf

echo "Nginx escuchará en el puerto ${PORT}."

echo "Iniciando PHP-FPM..."
php-fpm -D

echo "Iniciando Nginx..."
nginx -g 'daemon off;'
