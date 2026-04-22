# ======================================================================
# AlmhaBackendV2 — production Dockerfile
#
# Single container that runs nginx + php-fpm + queue worker under
# supervisord. Designed for Dokploy (or any Docker host behind a reverse
# proxy that terminates TLS). Exposes port 80.
#
# Build:
#   docker build -t almha-backend .
#
# Run:
#   docker run -p 8000:9000 --env-file .env almha-backend
#
# Dokploy: set the Dockerfile path to ./Dockerfile in the service config.
# ======================================================================

# ----------------------------------------------------------------------
# Stage 1: install composer dependencies
# ----------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

# Only copy files needed for `composer install` so Docker can cache this
# layer when application code changes but dependencies don't.
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# Copy the rest of the app so post-install autoload generation sees all classes.
COPY . .

RUN composer dump-autoload --no-dev --optimize --classmap-authoritative


# ----------------------------------------------------------------------
# Stage 2: runtime image
# ----------------------------------------------------------------------
FROM php:8.3-fpm-alpine AS runtime

# PHP runtime deps + build tools for native extensions
RUN apk add --no-cache \
        nginx \
        supervisor \
        bash \
        curl \
        icu-dev \
        oniguruma-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        postgresql-dev \
        mysql-client \
        tzdata \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        autoconf \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/* /var/tmp/*

# PHP configuration tuned for production
RUN { \
        echo 'opcache.memory_consumption=192'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.revalidate_freq=0'; \
        echo 'opcache.enable_cli=0'; \
        echo 'opcache.jit_buffer_size=64M'; \
        echo 'opcache.jit=tracing'; \
        echo 'memory_limit=256M'; \
        echo 'upload_max_filesize=20M'; \
        echo 'post_max_size=20M'; \
        echo 'expose_php=Off'; \
    } > /usr/local/etc/php/conf.d/zz-production.ini

WORKDIR /var/www/html

# Copy app (including installed vendor/) from the composer stage
COPY --from=vendor /app /var/www/html

# Copy infrastructure configs
COPY docker/nginx.conf       /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh    /usr/local/bin/entrypoint.sh

# Move php-fpm from default :9000 to :9001 so nginx can own :9000 externally.
# Also strip any CR (\r) that Git may have introduced on Windows, which would
# otherwise break the entrypoint shebang on Linux with "not found" errors.
RUN sed -i 's|^listen = 9000|listen = 127.0.0.1:9001|' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && sed -i 's/\r$//' /etc/nginx/http.d/default.conf \
    && sed -i 's/\r$//' /etc/supervisor/conf.d/supervisord.conf \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p /run/nginx /var/log/supervisor \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# Port 9000 for HTTP — Dokploy's Traefik forwards here after TLS termination.
EXPOSE 9000

# Health endpoint built into Laravel (see /up route in framework).
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -fsS http://127.0.0.1:9000/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
