FROM php:8.1-fpm

LABEL maintainer="Hasmukh Mistry (https://github.com/hasukmistry)"

RUN apt-get update -y

# Essentials
RUN apt-get -y install build-essential wget curl gnupg lsb-release gcc make autoconf libc-dev pkg-config

RUN apt-get install -y --no-install-recommends \
    libmagick++-dev \
    libmagickcore-dev libmagickwand-dev \
    libc6-dev libsybdb5 libpq-dev \
    libzip-dev zip \
    libgd-dev libpng++-dev libwebp-dev libjpeg-dev libjpeg62-turbo-dev libpng-dev libxpm-dev libvpx-dev libfreetype6-dev \
    libmcrypt-dev \
    libssh2-1-dev

RUN apt-get install -y freetds-bin

RUN apt-get install -y freetds-dev

RUN docker-php-ext-install exif
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pdo_dblib pdo_mysql pdo_pgsql
RUN docker-php-ext-install zip
RUN docker-php-ext-install bcmath

# install GD
RUN docker-php-ext-configure gd \
	--with-jpeg \
	--with-xpm \
	--with-webp \
	--with-freetype \
	&& docker-php-ext-install -j$(nproc) gd

# install pecl extension
RUN pecl install imagick && \
	docker-php-ext-enable imagick

RUN docker-php-ext-install intl

RUN apt-get update && apt-get install -y mariadb-client && rm -rf /var/lib/apt

# Install xdebug extension
RUN pecl install xdebug && docker-php-ext-enable xdebug

# install APCu
RUN pecl install apcu && docker-php-ext-enable apcu --ini-name docker-php-ext-10-apcu.ini

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install supervisor
RUN apt-get update -y && apt-get install -y --no-install-recommends supervisor

# Install opcache extension for PHP accelerator
RUN docker-php-ext-install opcache \
    && docker-php-ext-enable opcache \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get autoremove -y

# Clean up
RUN apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Using Debian, as root
RUN curl -fsSL https://deb.nodesource.com/setup_17.x | bash -
RUN apt-get install -y nodejs

# install global packages
ENV NPM_CONFIG_PREFIX=/home/node/.npm-global
ENV PATH $PATH:/home/node/.npm-global/bin:/app/node_modules/.bin/
ENV NODE_OPTIONS=--openssl-legacy-provider
RUN npm i -g npm@latest yarn@latest

# Add configuration files
ADD docker/config/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/
ADD docker/config/php/php.ini /usr/local/etc/php/

# Install symfony binary
RUN echo 'deb [trusted=yes] https://repo.symfony.com/apt/ /' | tee /etc/apt/sources.list.d/symfony-cli.list
RUN apt-get update -y
RUN apt-get install -y symfony-cli

# Composer & other directory tweaks
ARG VAR_HOME=/var/www
ARG COMPOSER_HOME=$VAR_HOME/.composer
ARG WEB_USER=www-data

RUN usermod -u 1000 $WEB_USER && groupmod -g 1000 $WEB_USER

RUN chown -R $WEB_USER:$WEB_USER $VAR_HOME
RUN mkdir $COMPOSER_HOME && chown -R $WEB_USER:$WEB_USER $COMPOSER_HOME
RUN chown -R $WEB_USER:$WEB_USER /usr/local/etc/php/conf.d

USER $WEB_USER

WORKDIR /var/www/app