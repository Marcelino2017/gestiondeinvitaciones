# =============================================================================
# Stage 1: PHP dependencies (Composer)
# =============================================================================
FROM composer:2.8 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# =============================================================================
# Stage 2: PHP runtime (production image)
# =============================================================================
FROM php:8.4-fpm-alpine AS app

LABEL org.opencontainers.image.title="GestionDeInvitaciones" \
      org.opencontainers.image.description="Laravel 13 application" \
      org.opencontainers.image.authors="GestionDeInvitaciones"

# Install system libraries and compile PHP extensions in a single layer.
# The virtual package (.build-deps) is removed afterwards to keep the image lean.
RUN apk add --no-cache \
        libpq \
        libzip \
        libxml2 \
        oniguruma \
        icu-libs \
        curl \
    && apk add --no-cache --virtual .build-deps \
        libpq-dev \
        libzip-dev \
        libxml2-dev \
        oniguruma-dev \
        icu-dev \
        curl-dev \
        autoconf \
        g++ \
        make \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        xml \
        curl \
        zip \
        bcmath \
        intl \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/pear

# Composer CLI: el entrypoint ejecuta `composer install` cuando el volumen vendor está vacío u obsoleto.
# La imagen php-fpm no incluye Composer; sin esto el contenedor falla con "composer: not found".
COPY --from=composer:2.8 /usr/bin/composer /usr/local/bin/composer

# Copy custom PHP configuration
COPY docker/php/php.ini $PHP_INI_DIR/conf.d/app.ini

WORKDIR /var/www/html

# Create an unprivileged user to run the application
RUN addgroup -g 1000 -S www \
    && adduser -u 1000 -S www -G www

# Copy application source (respects .dockerignore)
COPY --chown=www:www . .

# Overlay the production vendor from Stage 1
COPY --from=vendor --chown=www:www /app/vendor ./vendor

# Ensure writable directories exist with correct permissions
RUN mkdir -p \
        storage/logs \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chown -R www:www storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY --chown=www:www docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

USER www

EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
