# Utiliser une image de base avec PHP et Composer
FROM php:8.2-fpm

# Installer les extensions nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install sockets opcache pdo pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/symfony

# Copier les fichiers de l'application
COPY . .

# Installer les dépendances Composer
RUN composer install --no-dev --optimize-autoloader --classmap-authoritative --no-scripts

# Installer Xdebug (si non installé)
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Exposer le port sur lequel l'application Symfony écoute
EXPOSE 9000

# Commande pour démarrer le serveur PHP-FPM
CMD ["php-fpm"]
