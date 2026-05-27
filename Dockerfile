FROM php:8.2-apache

COPY . /var/www/html/

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN echo "DirectoryIndex home.php" >> /etc/apache2/apache2.conf

EXPOSE 80
