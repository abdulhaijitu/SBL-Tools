FROM php:8.2-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite pdo_mysql bcmath

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application files
COPY . .

# Set environment variables for production build
ENV APP_ENV=production
ENV APP_DEBUG=false

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install frontend dependencies and build assets
RUN npm ci && npm run build && rm -rf node_modules

# Setup storage directory permissions
RUN chmod -R 775 storage bootstrap/cache

# Expose server port
EXPOSE 8000

# Start script: ensure database exists, run migrations, and serve
CMD php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');" && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
