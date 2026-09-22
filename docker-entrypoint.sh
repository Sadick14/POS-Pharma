#!/bin/sh
set -e

cd /app

# Ensure SQLite file exists if using sqlite connection
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-/app/database/database.sqlite}"
    if [ ! -f "$DB_FILE" ]; then
        echo "Creating SQLite database file at $DB_FILE..."
        mkdir -p "$(dirname "$DB_FILE")"
        touch "$DB_FILE"
        chmod 666 "$DB_FILE"
    fi
fi

# Ensure storage directories exist and are writable
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

chmod -R 777 storage bootstrap/cache

# Generate APP_KEY if not already set
if [ -z "$APP_KEY" ]; then
    echo "Warning: APP_KEY not set. Generating application key..."
    php artisan key:generate --force
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Seed initial database if no users exist
echo "Checking and seeding initial records..."
php artisan db:seed --force || true

# Optimize cache for production if in production environment
if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Determine port (Railway sets PORT environment variable)
PORT_TO_BIND="${PORT:-8000}"
echo "============================================================"
echo " Starting PIMS Pharmacy Management System on port: $PORT_TO_BIND"
echo "============================================================"

exec php artisan serve --host=0.0.0.0 --port="$PORT_TO_BIND"
