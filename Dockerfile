FROM php:8.2-apache

COPY . /var/www/html/

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Railway necesita que Apache escuche en el puerto dinámico
ENV PORT=8080

RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf \
 && sed -i 's/80/${PORT}/g' /etc/apache2/sites-enabled/000-default.conf

EXPOSE 8080