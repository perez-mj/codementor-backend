FROM php:8.2-apache

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql

# enable mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy everything (including app/, routes, etc.)
COPY . .

# Set Apache DocumentRoot to /var/www/html/public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80
