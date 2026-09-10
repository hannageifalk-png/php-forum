FROM php:8.4-apache

RUN docker-php-ext-install mysqli pdo_mysql

RUN a2enmod rewrite

COPY html/ /var/www/html/

COPY docker-entrypoint.sh /docker-entrypoint.sh

RUN chmod +x /docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/docker-entrypoint.sh"]