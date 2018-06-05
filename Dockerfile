FROM php:7.1-apache

RUN apt-get update && \
    apt-get install -y \
        libmcrypt-dev \
        libxml2-dev \
        libldb-dev \
        libicu-dev \
        libmemcached-dev \
        libcurl4-openssl-dev \
        libssl-dev \
        curl \
        ssmtp \
        mysql-client \
        git \
        wget && \
    rm -rf /var/lib/apt/lists/* && \
    wget https://getcomposer.org/download/1.2.4/composer.phar -O /usr/local/bin/composer && \
    chmod a+rx /usr/local/bin/composer

RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd && \
    docker-php-ext-configure mysqli --with-mysqli=mysqlnd && \
    docker-php-ext-install pdo_mysql && \
    docker-php-ext-install mysqli && \
    docker-php-ext-install mcrypt && \
    docker-php-ext-install mbstring && \
    a2enmod rewrite && \
    sed -i 's!/var/www/html!/var/www/public!g' /etc/apache2/sites-available/000-default.conf && \
    mv /var/www/html /var/www/public && \
    pecl install mongodb && \
    pecl install memcached && \
    pecl install redis && \
    pecl install xdebug

WORKDIR /var/www
