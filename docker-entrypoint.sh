#!/bin/sh
set -e

cd /app

# Ensure .env file exists and is writable
if [ ! -f /app/.env ]; then
    if [ -f /app/.env.example ]; then
        cp /app/.env.example /app/.env
    else
        touch /app/.env
    fi
fi
chmod 666 /app/.env

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

# Generate APP_KEY if not set in environment variable or .env file
if [ -z "$APP_KEY" ]; then
    if ! grep -q "^APP_KEY=base64:" /app/.env 2>/dev/null; then
        echo "Generating application key..."
        php artisan key:generate --force
    fi
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Seed initial database records
echo "Checking and seeding initial records..."
php artisan db:seed --force || true

# Clear previous caches
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Determine port (Railway sets PORT environment variable)
PORT_TO_BIND="${PORT:-8000}"
echo "============================================================"
echo " Starting PIMS Pharmacy Management System on port: $PORT_TO_BIND"
echo "============================================================"

exec php artisan serve --host=0.0.0.0 --port="$PORT_TO_BIND"
