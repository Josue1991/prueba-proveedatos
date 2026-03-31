FROM php:8.2-apache

# Instala la extensión PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

# Copia código fuente
COPY . /var/www/html/

# VirtualHost personalizado
COPY apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
