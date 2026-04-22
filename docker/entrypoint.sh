#!/bin/sh
set -e

# ----------------------------------------------------------------------
# Entrypoint: runs on every container start. Idempotent — safe to re-run.
# ----------------------------------------------------------------------

cd /var/www/html

# --- Env sanity checks ---------------------------------------------------
if [ -z "$APP_KEY" ]; then
    echo "[entrypoint] APP_KEY is not set; generating a fresh one."
    php artisan key:generate --force
fi

# --- Storage permissions (needed on fresh volumes) ----------------------
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

# --- Storage symlink for public uploads ---------------------------------
if [ ! -L public/storage ]; then
    echo "[entrypoint] Creating storage symlink."
    php artisan storage:link || true
fi

# --- Run database migrations --------------------------------------------
# --force required in non-interactive mode; no-op if already applied.
echo "[entrypoint] Running migrations..."
php artisan migrate --force

# --- Cache config for performance ---------------------------------------
# These commands fail if APP env vars are missing at build time, so we do
# them at startup instead.
echo "[entrypoint] Caching config, routes, views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# --- Clear queued jobs from a previous container before starting worker --
php artisan queue:restart || true

# --- Hand off to supervisord (nginx + php-fpm + queue worker) ------------
echo "[entrypoint] Starting supervisord..."
exec "$@"
