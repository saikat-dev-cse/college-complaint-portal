# File: Dockerfile
FROM php:8.2-apache

# Install the MySQLi extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Set the document root to the "public" folder (Security best practice)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy your project files
COPY . /var/www/html/

# FIX: Create the uploads directory and set permissions for student photos/logos
RUN mkdir -p /var/www/html/public/uploads && \
    chmod -R 777 /var/www/html/public/uploads
