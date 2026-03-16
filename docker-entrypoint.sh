#!/bin/bash
set -e

# Copy .env if not exists
if [ ! -f /var/www/.env ]; then
    cp /var/www/.env.docker /var/www/.env
fi

# Generate app key if not set
php artisan key:generate --no-interaction --force

# Wait for MySQL to be ready
echo "Waiting for database..."
until mysqladmin ping -h "$DB_HOST" -u "$DB_USERNAME" -p"$DB_PASSWORD" --silent 2>/dev/null; do
    echo "  retrying in 3s..."
    sleep 3
done
echo "Database is ready."

# Run migrations
php artisan migrate --force --no-interaction

# Clear and cache config
php artisan config:clear
php artisan cache:clear

# Start server
exec php artisan serve --host=0.0.0.0 --port=8000
