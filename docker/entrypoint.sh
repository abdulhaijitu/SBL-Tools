#!/bin/sh
set -e

# Dynamically set listening port from Render's $PORT (default 8000)
PORT=${PORT:-8000}
sed -i "s/PORT_PLACEHOLDER/$PORT/g" /etc/nginx/http.d/default.conf

# Ensure required runtime directories exist and have proper permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/www/html/database

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Ensure APP_KEY is set and valid
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY not provided. Setting application key..."
    export APP_KEY="base64:jbGgydtFYDKPLRpynPVv4O4XgYQNxvMTVDzoSBWrbMY="
fi

# Database initialization
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    DB_PATH=${DB_DATABASE:-/var/www/html/database/database.sqlite}
    if [ ! -f "$DB_PATH" ]; then
        echo "Creating fresh SQLite database at $DB_PATH..."
        touch "$DB_PATH"
    fi
    chown www-data:www-data "$DB_PATH"
    chmod 664 "$DB_PATH"
    echo "Running SQLite migrations and database sync..."
    php artisan migrate --force
    php artisan db:seed --force
else
    echo "Running migrations and database sync for $DB_CONNECTION..."
    php artisan migrate --force
    php artisan db:seed --force
fi

# Ensure storage symlink exists
php artisan storage:link --force 2>/dev/null || true

# Production optimizations
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting Supervisor (Nginx + PHP-FPM) on port $PORT..."
exec /usr/bin/supervisord -c /etc/supervisord.conf

