FROM php:8.2-apache

RUN docker-php-ext-install mysqli

COPY public/ /var/www/html/
COPY config/ /var/www/html/config/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
