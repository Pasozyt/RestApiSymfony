FROM php:8.2-fpm AS php
COPY --from=composer:2.2.6 /usr/bin/composer /usr/bin/composer

# Install NPM
RUN apt-get update && apt-get install -y curl gnupg
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get update && apt-get install nodejs -y

WORKDIR "/application"

RUN apt-get update && apt-get install -y zlib1g-dev g++ git libicu-dev libzip-dev zip \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

ENV TZ=Europe/Warsaw

EXPOSE 9000

FROM nginx:1.27.3-perl AS nginx
COPY ./infrastructure/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./infrastructure/nginx/default.conf /etc/nginx/templates/default.conf.template

# Remove APT cache
RUN apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*