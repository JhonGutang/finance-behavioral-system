#!/bin/sh
set -e

echo "🚀 Starting Finance Behavioral System Backend..."

# Wait for database to be ready
echo "⏳ Waiting for database connection..."
until php artisan db:show 2>/dev/null; do
    echo "Database not ready, waiting..."
    sleep 2
done

echo "✅ Database connection established"

# Run Laravel setup tasks
echo "📦 Setting up Laravel application..."

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Cache configuration and routes for production
echo "🔧 Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force --no-interaction

# Set proper permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Start supervisor to manage PHP-FPM and Nginx
echo "✨ Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
