# ======================================================================
# AlmhaBackendV2 — production Dockerfile (Laravel Octane + Swoole)
#
# Serves HTTP directly on :9000 via Octane. No nginx / php-fpm needed.
# Designed for Dokploy (Traefik terminates TLS, forwards to :9000).
#
# Requirements before building:
#   composer require laravel/octane
#   php artisan octane:install --server=swoole
#
# Build:  docker build -t almha-backend .
# Run:    docker run -p 9000:9000 --env-file .env almha-backend
# ======================================================================

FROM php:8.4-cli AS base

# ---------------------------------------------------------------------
# System deps + PHP extensions
# ---------------------------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl procps netcat-openbsd \
        libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev \
        libcurl4-openssl-dev libssl-dev zlib1g-dev libicu-dev \
        g++ libevent-dev libfreetype6-dev libjpeg62-turbo-dev libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        gd pdo pdo_mysql pdo_pgsql pgsql ftp mbstring zip exif pcntl bcmath sockets intl opcache \
    && pecl install redis swoole \
    && docker-php-ext-enable redis swoole \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------------------
# Production PHP config
# ---------------------------------------------------------------------
RUN { \
        echo 'opcache.memory_consumption=192'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.jit=tracing'; \
        echo 'opcache.jit_buffer_size=64M'; \
        echo 'memory_limit=256M'; \
        echo 'upload_max_filesize=20M'; \
        echo 'post_max_size=20M'; \
        echo 'expose_php=Off'; \
    } > /usr/local/etc/php/conf.d/zz-production.ini

# ---------------------------------------------------------------------
# Composer
# ---------------------------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# ---------------------------------------------------------------------
# Install dependencies (cached layer — only rebuilds if composer.* changes)
# ---------------------------------------------------------------------
COPY composer.json composer.lock artisan ./

RUN mkdir -p bootstrap/cache \
        storage/app/public/images \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
    && composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# ---------------------------------------------------------------------
# Copy the rest of the app and regenerate autoload with all classes
# ---------------------------------------------------------------------
COPY . .

RUN composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && php artisan storage:link || true \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -fsS http://127.0.0.1:9000/up || exit 1

# Run migrations + cache + Octane. If the DB isn't reachable yet the container
# stays up anyway so logs are inspectable in Dokploy.
CMD ["sh", "-c", "\
    php artisan migrate --force || echo '[start] migrate failed, continuing' && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    exec php artisan octane:start --server=swoole --host=0.0.0.0 --port=9000 \
"]
