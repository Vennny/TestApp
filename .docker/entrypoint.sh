#!/bin/sh
chown -R www-data:www-data /var/www/api/storage /var/www/api/bootstrap/cache
chmod -R 775 /var/www/api/storage /var/www/api/bootstrap/cache
exec docker-php-entrypoint "$@"
