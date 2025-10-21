#!/bin/bash
# docker/development/entrypoint.sh

set -e

# Generate APP_KEY if not set
if [ -z "${APP_KEY}" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --show --no-ansi | grep -oE 'base64:[^ ]+' > /tmp/app_key
    echo "APP_KEY=$(cat /tmp/app_key)" >> .env
    rm /tmp/app_key
else
    echo "APP_KEY already set"
fi

# Run migrations (dev only)
echo "Running migrations..."
php artisan migrate --force

# Seed database (dev only)
echo "Seeding database..."
php artisan db:seed --force

# Start PHP-FPM and Nginx
echo "Starting PHP-FPM and Nginx..."
php-fpm -D && nginx -g 'daemon off;'
