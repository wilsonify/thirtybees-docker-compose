FROM php:7.1-fpm
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        libcurl3-dev \
        libxml2-dev \
        # for imap
        libc-client-dev libkrb5-dev \
    && docker-php-ext-install -j$(nproc) iconv mcrypt curl bcmath xml json zip pdo_mysql mbstring \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install -j$(nproc) imap \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

# Install memcached
RUN apt-get update && apt-get install -y libmemcached-dev zlib1g-dev git

RUN git clone -b php7 https://github.com/php-memcached-dev/php-memcached /usr/src/php/ext/memcached \
    && docker-php-ext-configure /usr/src/php/ext/memcached \
        --disable-memcached-sasl \
    && docker-php-ext-install /usr/src/php/ext/memcached \
    && rm -rf /usr/src/php/ext/memcached

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

COPY ./www/thirtybees /var/www/thirtybees
WORKDIR /var/www/thirtybees
RUN COMPOSER=composer/php8.2/composer.json composer install
ENTRYPOINT [ "php" ]
CMD  [ "install-dev/index_cli.php", "--newsletter=1", "--language=en", "--country=us", "--db_name=thirtybees", "--db_create=1", "--name=thirtybees", "--email=test@thirty.bees", "--firstname=thirty", "--lastname=bees", "--password=thirtybees" ]