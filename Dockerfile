# ==========================================
# Stage 1: Build Frontend Assets (Vite)
# ==========================================
FROM node:20-alpine AS frontend-builder
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/

RUN npm run build

# ==========================================
# Stage 2: Install PHP Composer Dependencies
# ==========================================
FROM composer:2 AS composer-builder
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# ==========================================
# Stage 3: Production Runtime (Nginx + PHP-FPM)
# ==========================================
FROM php:8.2-fpm-alpine

# Install system dependencies & PHP build dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite-dev \
    postgresql-dev \
    oniguruma-dev

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
    pdo \
    pdo_sqlite \
    pdo_pgsql \
    pdo_mysql \
    bcmath \
    gd \
    zip \
    opcache

WORKDIR /var/www/html

ENV APP_KEY=base64:jbGgydtFYDKPLRpynPVv4O4XgYQNxvMTVDzoSBWrbMY=

# Copy application source code
COPY . .

# Copy installed composer vendors from Stage 2
COPY --from=composer-builder /app/vendor/ ./vendor/

# Copy compiled frontend assets from Stage 1
COPY --from=frontend-builder /app/public/build/ ./public/build/

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php.ini $PHP_INI_DIR/conf.d/custom.ini
COPY docker/entrypoint.sh /entrypoint.sh

# Fix line endings & permissions for the entrypoint script
RUN sed -i 's/\r$//' /entrypoint.sh && \
    chmod +x /entrypoint.sh

# Set directory permissions for Laravel
RUN mkdir -p storage bootstrap/cache database && \
    chown -R www-data:www-data storage bootstrap/cache database && \
    chmod -R 775 storage bootstrap/cache database

# Expose Render default port
EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
