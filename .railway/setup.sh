#!/bin/bash
set -e

echo "🚀 Railway Deployment Setup"

# Create storage directories
echo "📁 Creating storage directories..."
mkdir -p /app/storage/database
mkdir -p /app/storage/framework/cache
mkdir -p /app/storage/framework/sessions
mkdir -p /app/storage/framework/views
mkdir -p /app/storage/logs
mkdir -p /app/bootstrap/cache

# Create SQLite database
echo "📊 Creating SQLite database..."
touch /app/storage/database/database.sqlite
chmod 664 /app/storage/database/database.sqlite

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 /app/storage
chmod -R 775 /app/bootstrap/cache

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Create superadmin user with hardcoded credentials
echo "👤 Creating superadmin user..."
php artisan superadmin:create

# Cache config and routes
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache

echo "✅ Setup complete!"