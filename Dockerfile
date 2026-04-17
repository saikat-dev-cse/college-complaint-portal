# Use the official PHP 8 image with Apache web server
FROM php:8.2-apache

# Install the MySQLi extension for database connection
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Tell Apache that the website starts inside the "public" folder (Security Best Practice)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Enable Apache URL rewriting
RUN a2enmod rewrite

# Copy all your project files into the server
COPY . /var/www/html/

# Give the server permission to save student files in the uploads folder
RUN chmod -R 777 /var/www/html/public/uploads
