FROM php:8.4-apache

RUN docker-php-ext-install mysqli pdo_mysql

RUN a2enmod rewrite

RUN a2dismod mpm_event mpm_worker || true
RUN a2enmod mpm_prefork

COPY html/ /var/www/html/

EXPOSE 80