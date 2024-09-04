FROM php:7.4-fpm
RUN apt-get update && apt-get install -y  \
    git \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    libcurl3-dev \
    libxml2-dev \
    libzip-dev \
    libc-client-dev \
    libkrb5-dev \
    libmemcached-dev \
    zlib1g-dev \
    libonig-dev


RUN docker-php-ext-install -j$(nproc) iconv curl bcmath xml json zip pdo_mysql mbstring
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl
RUN docker-php-ext-install -j$(nproc) imap
RUN docker-php-ext-configure gd
RUN docker-php-ext-install -j$(nproc) gd

RUN git clone https://github.com/php-memcached-dev/php-memcached /usr/src/php/ext/memcached
RUN docker-php-ext-configure /usr/src/php/ext/memcached --disable-memcached-sasl
RUN docker-php-ext-install /usr/src/php/ext/memcached
RUN rm -rf /usr/src/php/ext/memcached

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

COPY ./www/thirtybees /var/www/thirtybees
WORKDIR /var/www/thirtybees
RUN COMPOSER=composer/php7.4/composer.json composer install
ENTRYPOINT [ "php" ]
CMD  [ "install-dev/index_cli.php", "--newsletter=1", "--language=en", "--country=us", "--db_name=thirtybees", "--db_create=1", "--name=thirtybees", "--email=test@thirty.bees", "--firstname=thirty", "--lastname=bees", "--password=thirtybees" ]