FROM php:7.1-apache

RUN apt-get update && \
    apt-get install -y \
        libmcrypt-dev \
        libxml2-dev \
        zlib1g-dev \
        locales \
        sudo \
        apt-utils \
        ca-certificates \
        libldb-dev \
        libicu-dev \
        libmemcached-dev \
        libcurl4-openssl-dev \
        libssl-dev \
        curl \
        ssmtp \
        mysql-client \
        git \
        gnupg \
        wget && \
    curl -sL https://deb.nodesource.com/setup_10.x | sudo bash - && \
    apt-get install -y \
        build-essential \
        nodejs \
        npm && \
    rm -rf /var/lib/apt/lists/* && \
    locale-gen "en_US.UTF-8" && \
    useradd --home /home/bus115 -m -N --uid 1000 bus115 && \
    usermod -a -G www-data bus115 && \
    usermod -a -G sudo bus115 && \
    wget https://getcomposer.org/download/1.2.4/composer.phar -O /usr/local/bin/composer && \
    chmod a+rx /usr/local/bin/composer

RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd && \
    docker-php-ext-configure mysqli --with-mysqli=mysqlnd && \
    docker-php-ext-install pdo_mysql && \
    docker-php-ext-install mysqli && \
    docker-php-ext-install mcrypt && \
    docker-php-ext-install mbstring && \
    docker-php-ext-install zip && \
    a2enmod rewrite && \
    sed -i 's!/var/www/html!/var/www/public!g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's!/var/www/html!/var/www/public!g' /etc/apache2/sites-available/default-ssl.conf && \
    mv /var/www/html /var/www/public && \
    pecl install mongodb && \
    pecl install memcached && \
    pecl install redis

RUN npm install apidoc -g && \
    a2ensite /etc/apache2/sites-available/default-ssl.conf && \
    service apache2 reload

RUN pecl install xdebug-2.5.0 \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_connect_back=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_port=9001" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.idekey=docker" >> /usr/local/etc/php/conf.d/xdebug.ini

ADD http://www.zlib.net/zlib-1.2.11.tar.gz /tmp/zlib.tar.gz
RUN tar zxpf /tmp/zlib.tar.gz -C /tmp && \
    cd /tmp/zlib-1.2.11 && \
    ./configure --prefix=/usr/local/zlib && \
    make && make install && \
    rm -Rf /tmp/zlib-1.2.11 && \
    rm /tmp/zlib.tar.gz

EXPOSE 80 443
WORKDIR /var/www
