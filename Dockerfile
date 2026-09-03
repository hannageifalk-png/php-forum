FROM php:8.4-apache

RUN docker-php-ext-install mysqli pdo_mysql

RUN a2enmod rewrite

COPY html/ /var/www/html/