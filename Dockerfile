FROM php:8.2-apache

# Install PDO
RUN docker-php-ext-install pdo pdo_mysql

# Enable mod_rewrite
RUN a2enmod rewrite

# Change Apache DocumentRoot → public/
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#g' \
    /etc/apache2/sites-available/000-default.conf

# Allow .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set the working directory
WORKDIR /var/www/html

# Copy entire project to container
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
