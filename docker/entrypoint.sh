#!/bin/sh
set -e

# =============================================================================
# Container entrypoint for GestionDeInvitaciones (Laravel)
# =============================================================================

echo "[entrypoint] Environment: ${APP_ENV:-local}"

# ---------------------------------------------------------------------------
# In development the vendor directory is a named volume.
# Install dependencies when the volume is empty (first run).
# ---------------------------------------------------------------------------
if [ ! -f "vendor/autoload.php" ]; then
    echo "[entrypoint] vendor/ missing — running composer install ..."
    # Keep container installs aligned with Dockerfile:
    # avoid running Composer scripts (e.g. boost:update) that may not exist in containers.
    composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts
fi

# ---------------------------------------------------------------------------
# Laravel caches can get out of sync when we mount source code from the host.
# In particular, services.php/packages.php may reference dev-only providers
# (like Pail) that aren't installed inside the container image.
# Clearing them avoids boot-time "Class not found" crashes.
# ---------------------------------------------------------------------------
if [ -d "bootstrap/cache" ]; then
    echo "[entrypoint] Clearing bootstrap/cache/*.php ..."
    rm -f bootstrap/cache/*.php || true
fi

mkdir -p bootstrap/cache

# ---------------------------------------------------------------------------
# Generate APP_KEY if none is set (first-run safety net)
# ---------------------------------------------------------------------------
if [ -z "$APP_KEY" ]; then
    echo "[entrypoint] APP_KEY is empty — generating ..."
    php artisan key:generate --force
fi

# ---------------------------------------------------------------------------
# Run database migrations (only when enabled)
# This avoids race conditions because multiple containers (app/queue/scheduler)
# share the same entrypoint in this compose file.
# ---------------------------------------------------------------------------
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "[entrypoint] Running migrations ..."
    php artisan migrate --force || true
else
    echo "[entrypoint] Skipping migrations (RUN_MIGRATIONS=${RUN_MIGRATIONS:-false})"
fi

# ---------------------------------------------------------------------------
# Production optimisations
# ---------------------------------------------------------------------------
if [ "$APP_ENV" = "production" ]; then
    echo "[entrypoint] Caching config, routes, views and events ..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
fi

echo "[entrypoint] Starting: $*"
exec "$@"
