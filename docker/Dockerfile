FROM php:7-apache

ENV APP_ROOT /opt/app
ENV APACHE_DOCUMENT_ROOT $APP_ROOT/public

WORKDIR $APP_ROOT

RUN apt-get update \
    && apt-get install -y libpq-dev libicu-dev \
    && docker-php-ext-install -j$(nproc) intl \
    && docker-php-ext-install -j$(nproc) pdo_pgsql \
    && mkdir -p "$APP_ROOT" \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
