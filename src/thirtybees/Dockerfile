FROM php:8.3.36-fpm-bookworm AS phpbuild

# Pinned build tools. Both archives are checked against the SHA-256 values
# below, so an upstream tag or installer change fails the build.
ARG COMPOSER_VERSION=2.10.3
ARG COMPOSER_SHA256=7a2d379d5b8ffdaa028580ef26494c36d2feef4b178d3dd1473a4dbc5e17c8d6
ARG MEMCACHED_VERSION=3.4.0
ARG MEMCACHED_SHA256=c163434eb0da97c8f45c7ad41d979d381f8b81c49402b1b90b063987fb37972e

# Install necessary dependencies and PHP extensions
RUN apt-get update && apt-get install -y  \
    ca-certificates \
    curl \
    git \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    libzip-dev \
    libc-client-dev \
    libkrb5-dev \
    libmemcached-dev \
    zlib1g-dev \
    libonig-dev \
    libicu-dev \
    nginx \
    mariadb-client \
    supervisor \
    && docker-php-ext-install -j$(nproc) bcmath \
    && docker-php-ext-install -j$(nproc) curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install -j$(nproc) imap \
    && docker-php-ext-install -j$(nproc) intl \
    && docker-php-ext-install -j$(nproc) mbstring \
    && docker-php-ext-install -j$(nproc) pdo_mysql \
    && docker-php-ext-install -j$(nproc) soap \
    && docker-php-ext-install -j$(nproc) xml \
    && docker-php-ext-install -j$(nproc) zip \
    && curl -fsSL -o /tmp/memcached.tgz "https://pecl.php.net/get/memcached-${MEMCACHED_VERSION}.tgz" \
    && echo "${MEMCACHED_SHA256}  /tmp/memcached.tgz" | sha256sum -c - \
    && mkdir -p /tmp/memcached /usr/src/php/ext/memcached \
    && tar -xzf /tmp/memcached.tgz -C /tmp/memcached \
    && cp -a "/tmp/memcached/memcached-${MEMCACHED_VERSION}/." /usr/src/php/ext/memcached/ \
    && docker-php-ext-configure /usr/src/php/ext/memcached --disable-memcached-sasl \
    && docker-php-ext-install /usr/src/php/ext/memcached \
    && rm -rf /tmp/memcached /tmp/memcached.tgz /usr/src/php/ext/memcached

# Install Composer from the pinned release archive.
RUN curl -fsSL -o /usr/local/bin/composer "https://getcomposer.org/download/${COMPOSER_VERSION}/composer.phar" \
    && echo "${COMPOSER_SHA256}  /usr/local/bin/composer" | sha256sum -c - \
    && chmod +x /usr/local/bin/composer \
    && composer --version

# bootstrap files
COPY default.conf /etc/nginx/conf.d/default.conf
RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/sites-available/default
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY install.sh /usr/local/bin/install-thirtybees.sh
RUN chmod +x /usr/local/bin/install-thirtybees.sh
COPY --chown=www-data:www-data ./www/ /var/www/default/
WORKDIR /var/www/default

# The source is fetched before the build (make deps or fetch-thirtybees.sh).
# Fail with a useful message instead of a surprising 404 from the installer.
# Composer installs from the committed lock file; writable directories get the
# web-server ownership.
RUN set -e; \
    test -f install-dev/index_cli.php \
      || { echo "ThirtyBees source is missing. Run bash fetch-thirtybees.sh before docker build." >&2; exit 1; }; \
    rm -f .gitkeep; \
    COMPOSER=composer/php8.3/composer.json composer install --no-dev --no-interaction --prefer-dist; \
    mkdir -p cache log upload download; \
    chown -R www-data:www-data config cache img log mails modules themes translations upload download

# Expose port 80 for Nginx
EXPOSE 80

# Start supervisor to manage both PHP-FPM and Nginx
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
