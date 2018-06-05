FROM php:7.1-apache

RUN apt-get update && \
    apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libxml2-dev \
        zlib1g-dev \
        libldb-dev \
        libicu-dev \
        libmemcached-dev \
        libcurl4-openssl-dev \
        libssl-dev \
        libsqlite3-dev \
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
    docker-php-ext-install pdo_sqlite && \
    docker-php-ext-install intl && \
    docker-php-ext-install mcrypt && \
    docker-php-ext-install mbstring && \
    docker-php-ext-install zip && \
    docker-php-ext-install ftp && \
    docker-php-ext-install sockets && \
    a2enmod rewrite && \
    sed -i 's!/var/www/html!/var/www/public!g' /etc/apache2/sites-available/000-default.conf && \
    mv /var/www/html /var/www/public && \
    pecl install mongodb && \
    pecl install memcached && \
    pecl install redis && \
    pecl install xdebug

ADD http://www.zlib.net/zlib-1.2.11.tar.gz /tmp/zlib.tar.gz
RUN tar zxpf /tmp/zlib.tar.gz -C /tmp && \
    cd /tmp/zlib-1.2.11 && \
    ./configure --prefix=/usr/local/zlib && \
    make && make install && \
    rm -Rf /tmp/zlib-1.2.11 && \
    rm /tmp/zlib.tar.gz

ADD https://blackfire.io/api/v1/releases/probe/php/linux/amd64/56 /tmp/blackfire-probe.tar.gz
RUN tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp && \
    mv /tmp/blackfire-*.so `php -r "echo ini_get('extension_dir');"`/blackfire.so && \
    rm /tmp/blackfire-probe.tar.gz

WORKDIR /var/www
