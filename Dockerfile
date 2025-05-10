# Dockerfile

# 1) On part de l’image PHP-FPM officielle
FROM php:8.1-fpm

# 2) Installer git, unzip, zip et l’extension PDO MySQL
RUN apt-get update \
 && apt-get install -y git unzip zip \
 && docker-php-ext-install pdo pdo_mysql

# 3) Installer Composer à partir de l’image officielle Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4) Définir le répertoire de travail
WORKDIR /var/www/html

# 5) Copier le code de l’hôte vers le container
COPY . .

# (Optionnel) Si tu veux installer les dépendances dès le build :
# RUN composer install --no-dev --optimize-autoloader

# 6) Exposer le volume de code (Docker Compose le gérera déjà)
VOLUME ["/var/www/html"]