FROM php:8.4-apache

# One layer, so the apt lists are removed in the same step that downloads them.
# git and unzip are for Composer; the MySQL client is for poking at the database from ./docker ssh.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        default-mysql-client \
        nano \
        vim \
    && docker-php-ext-install zip pdo_mysql \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

# PHP's development settings: errors shown, assertions on. This image is the development environment.
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY ./docker-conf/php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"

# Debian's mysql client is MariaDB's, which rejects MySQL 8.4's self-signed certificate.
# The connection stays encrypted; only the certificate check is off, for `mysql -h db`.
RUN printf '[client]\nssl-verify-server-cert = off\n' > /etc/mysql/conf.d/client.cnf

# Composer runs inside the container (./docker-run composer install), so the host needs no PHP.
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# The source is not copied in: docker-compose.yml mounts the repo at /var/www/html, so the
# image only holds the runtime. A production image would COPY the code and run
# `composer install --no-dev` here instead.
COPY ./docker-conf/httpd.conf /etc/apache2/sites-available/000-default.conf
