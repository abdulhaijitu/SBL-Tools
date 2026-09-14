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
# Detect connection from DATABASE_URL if present (Render Postgres, Supabase, Neon, etc.)
if [ -n "$DATABASE_URL" ]; then
    case "$DATABASE_URL" in
        postgres://*|postgresql://*)
            export DB_CONNECTION="pgsql"
            ;;
        mysql://*)
            export DB_CONNECTION="mysql"
            ;;
    esac
fi

# SQLite Persistent directory support (Render Disks, Docker Volume mounts)
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    if [ -d "/var/data" ]; then
        PERSISTENT_DIR="/var/data"
    elif [ -d "/data" ]; then
        PERSISTENT_DIR="/data"
    else
        PERSISTENT_DIR="/var/www/html/database"
    fi

    mkdir -p "$PERSISTENT_DIR"
    chown -R www-data:www-data "$PERSISTENT_DIR" 2>/dev/null || true
    chmod -R 775 "$PERSISTENT_DIR" 2>/dev/null || true

    if [ -z "$DB_DATABASE" ] || [ "$DB_DATABASE" = "/var/www/html/database/database.sqlite" ]; then
        DB_PATH="$PERSISTENT_DIR/database.sqlite"
        export DB_DATABASE="$DB_PATH"
    else
        DB_PATH="$DB_DATABASE"
    fi

    if [ ! -f "$DB_PATH" ]; then
        echo "Creating initial SQLite database at $DB_PATH..."
        touch "$DB_PATH"
    fi
    chown www-data:www-data "$DB_PATH" 2>/dev/null || true
    chmod 664 "$DB_PATH" 2>/dev/null || true
fi

echo "Running migrations safely (schema updates only)..."
php artisan migrate --force

# Check if database is already initialized to PREVENT overwriting live data
USER_COUNT=$(php artisan tinker --execute="try { echo \Illuminate\Support\Facades\Schema::hasTable('users') ? \App\Models\User::count() : 0; } catch (\Throwable \$e) { echo 0; }" 2>/dev/null | tr -d '\r\n')

if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo "Fresh database detected: Seeding initial baseline data..."
    php artisan db:seed --force
else
    echo "Existing database detected ($USER_COUNT users found). Preserving existing live data without overwriting!"
    # Only ensure required system roles & permissions exist, never overwrite user data
    php artisan db:seed --class=RoleAndPermissionSeeder --force || true
    php artisan db:seed --class=MemberRoleSeeder --force || true
fi

# Ensure storage symlink exists
php artisan storage:link --force 2>/dev/null || true

# Production optimizations
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting Supervisor (Nginx + PHP-FPM) on port $PORT..."
exec /usr/bin/supervisord -c /etc/supervisord.conf

