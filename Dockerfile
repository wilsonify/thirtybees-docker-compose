FROM php:8.3-fpm AS phpbuild

# Install necessary dependencies
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
    libonig-dev \
    libicu-dev \
    nginx \
    supervisor

# PHP extensions
RUN docker-php-ext-install -j$(nproc) bcmath
RUN docker-php-ext-install -j$(nproc) curl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-install -j$(nproc) iconv
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl && docker-php-ext-install -j$(nproc) imap
RUN docker-php-ext-install -j$(nproc) intl

RUN docker-php-ext-install -j$(nproc) mbstring
RUN docker-php-ext-install -j$(nproc) pdo_mysql
RUN docker-php-ext-install -j$(nproc) soap
RUN docker-php-ext-install -j$(nproc) xml
RUN docker-php-ext-install -j$(nproc) zip

# Install memcached PHP extension
RUN git clone https://github.com/php-memcached-dev/php-memcached /usr/src/php/ext/memcached
RUN docker-php-ext-configure /usr/src/php/ext/memcached --disable-memcached-sasl
RUN docker-php-ext-install /usr/src/php/ext/memcached
RUN rm -rf /usr/src/php/ext/memcached

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# bootstrap files
COPY default.conf /etc/nginx/conf.d/default.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./www/thirtybees /var/www/default
WORKDIR /var/www/default
RUN chown -R www-data:www-data /var/www/default
RUN chmod -R 755 /var/www/default

# Install thirtybees
RUN COMPOSER=composer/php8.3/composer.json composer install
RUN php install-dev/index_cli.php  \
--activity=0 \
--all_languages=0 \
--base_uri=/  \
--country=us  \
--db_clear=1 \
--db_create=1  \
--db_name=thirtybees \
--db_server=mysql \
--db_user=thirtybees \
--domain=localhost:9000  \
--email=pub@thirtybees.com \
--email=test@thirty.bees  \
--engine=mariadb \
--firstname=John \
--language=en  \
--lastname=bees  \
--license==0 \
--name=thirty-bees \
--newsletter=1  \
--password=thirtybees \
--prefix=tb_ \
--send_email=1 \
--step=all \
--timezone=US/Eastern

# Expose port 80 for Nginx
EXPOSE 80

# Start supervisor to manage both PHP-FPM and Nginx
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
