FROM php:8.2-apache

COPY . /var/www/html/

RUN docker-php-ext-install mysqli pdo pdo_mysql

# SOLO esto
ENV PORT=8080

EXPOSE 80