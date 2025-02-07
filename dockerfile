FROM php:8.1.4-apache
RUN apt-get update && apt-get install -y nano
#packages for database backup
RUN docker-php-ext-install pdo_mysql
RUN apt install default-mysql-client -y

WORKDIR /var/www/html
COPY .htaccess .htaccess

COPY . /var/www/html/cwd-files
RUN a2enmod rewrite