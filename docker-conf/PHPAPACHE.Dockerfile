FROM php:7.4-apache

# Install Vim and Nano
RUN apt-get update
RUN apt-get install nano
RUN apt-get install vim -y

RUN apt-get install libzip-dev -y
RUN apt-get install zip -y
RUN apt-get install default-mysql-client -y

# Install php extensions
RUN docker-php-ext-install zip
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install git
RUN apt-get -y install git

# Add mod_rewrite module
RUN a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy stuff into container
COPY src /var/www/html/
COPY ./docker-conf/httpd.conf /etc/apache2/sites-available/000-default.conf
