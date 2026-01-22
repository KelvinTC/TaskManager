#!/bin/bash
set -e

echo "🚀 Starting Railway deployment..."

# Create storage/database directory if it doesn't exist
mkdir -p /app/storage/database

# Create SQLite database file if it doesn't exist
if [ ! -f /app/storage/database/database.sqlite ]; then
    echo "📁 Creating SQLite database file..."
    touch /app/storage/database/database.sqlite
    chmod 664 /app/storage/database/database.sqlite
fi

# Set proper permissions
chmod -R 775 /app/storage
chmod -R 775 /app/bootstrap/cache

echo "🔧 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "🗄️  Running migrations..."
php artisan migrate --force

echo "🌱 Running database seeders..."
php artisan db:seed --force --class=SuperAdminSeeder

echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache

echo "✅ Deployment complete!"