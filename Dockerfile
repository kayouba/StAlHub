FROM php:8.1-fpm

# 1) Installer les extensions et Composer
RUN apt-get update \
 && apt-get install -y git unzip zip libzip-dev libonig-dev libxml2-dev \
 && docker-php-ext-install pdo_mysql zip mbstring xml \
 && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# 2) Forcer PHP-FPM à écouter sur le port TCP 9000
RUN sed -i "s|^listen = .*|listen = 0.0.0.0:9000|" /usr/local/etc/php-fpm.d/www.conf \
 && sed -i "s|^;listen.mode = .*|listen.mode = 0666|" /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

# 3) On copie tout, on installe les dépendances en buildtime
COPY . .

RUN composer install --no-dev --no-interaction --optimize-autoloader

# 4) Lancement par défaut
CMD ["php-fpm", "-F"]
