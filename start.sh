#!/bin/bash
set -e

echo "🚀 Starting Laravel application..."

# Run setup script
bash /app/.railway/setup.sh

# Start queue worker in background
echo "🔄 Starting queue worker..."
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --daemon &

# Start PHP-FPM
echo "🌐 Starting PHP-FPM..."
php-fpm -D

# Start Nginx (foreground)
echo "🚀 Starting Nginx..."
nginx -g 'daemon off;'