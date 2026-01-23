# Multi-stage build for Laravel on Render Free Tier
# Stage 1: Build dependencies
FROM php:8.2-fpm-alpine AS builder

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    icu-dev \
    curl \
    git \
    unzip \
    nginx \
    supervisor

# Install PHP extensions required by Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies (production only)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy application code
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev

# Stage 2: Production image
FROM php:8.2-fpm-alpine

# Install runtime dependencies only
RUN apk add --no-cache \
    postgresql-libs \
    libzip \
    libpng \
    libjpeg-turbo \
    freetype \
    oniguruma \
    icu-libs \
    nginx \
    supervisor

# Copy PHP extensions from builder
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# Set working directory
WORKDIR /var/www/html

# Copy application from builder
COPY --from=builder --chown=www-data:www-data /var/www/html /var/www/html

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/start.sh /usr/local/bin/start.sh

# Make start script executable
RUN chmod +x /usr/local/bin/start.sh

# Create necessary directories and set permissions
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/api/health || exit 1

# Start services using the startup script
CMD ["/usr/local/bin/start.sh"]
