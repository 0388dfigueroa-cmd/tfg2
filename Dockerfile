FROM php:8.2-apache

COPY . /var/www/html/

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN printf '<Directory /var/www/html>\nDirectoryIndex home.php\n</Directory>' > /etc/apache2/conf-available/home.conf \
    && a2enconf home

EXPOSE 80