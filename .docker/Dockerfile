FROM php:8.5-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/api

COPY api/ .

RUN composer install --no-interaction --optimize-autoloader

RUN chown -R www-data:www-data /var/www/api/storage /var/www/api/bootstrap/cache \
    && chmod -R 775 /var/www/api/storage /var/www/api/bootstrap/cache

COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN echo -e "\nupload_max_filesize = 100M\npost_max_size = 100M" >> /usr/local/etc/php/php.ini

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]