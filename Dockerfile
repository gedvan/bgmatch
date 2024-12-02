# FIRST STAGE - app building.
FROM composer:2 AS builder

ENV APP_ROOT=/code

# Copy application.
COPY . $APP_ROOT
WORKDIR $APP_ROOT

# Install Composer dependencies.
RUN composer install --no-dev

# Install NodeJS and build frontend.
RUN apk add nodejs npm && \
    npm install && \
    npm run build


# SECOND STAGE - Server and app setup.
FROM php:8.3-apache

ENV APP_ROOT=/code
ENV APACHE_DOCUMENT_ROOT=$APP_ROOT/public

# Copy application from builder stage.
COPY --from=builder $APP_ROOT $APP_ROOT
WORKDIR $APP_ROOT

# Setup storage folder.
RUN mkdir -p storage/app \
      storage/framework/cache/data \
      storage/framework/views \
      storage/logs && \
    chown -R www-data:www-data storage

# Install PHP extensions and setup Apache.
RUN apt-get update \
    && apt-get install -y libpq-dev libicu-dev \
    && docker-php-ext-install -j$(nproc) intl \
    && docker-php-ext-install -j$(nproc) pdo_pgsql \
    && a2enmod rewrite \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Setup DB connection and other env vars.
# COPY .env.example $APP_ROOT/.env
# ENV APP_ENV=prod
# ENV APP_DEBUG=false
# ENV APP_URL=http://localhost:8000
# ENV JWT_SECRET=qwertyuiopasdfghjklzxcvbnm
# ENV DB_HOST=db
# ENV DB_DATABASE=bgmatch
# ENV DB_USERNAME=bgmatch
# ENV DB_PASSWORD=bgpass

# Run migrations.
# RUN php artisan migrate
