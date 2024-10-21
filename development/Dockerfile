FROM php:8.2-fpm

# Install required system packages and Xdebug if not already installed, continue if installed
RUN apt-get update && apt-get install -y zip unzip wget \
    && (pecl install xdebug || echo "Xdebug is already installed") \
    && docker-php-ext-enable xdebug

# Install MySQLi, PDO, and PDO_MYSQL extensions, update if needed, and continue if already installed
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    || echo "MySQLi, PDO, and PDO_MYSQL are already installed"