#!/bin/bash
set -ex  # Exit on error AND print each command

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

# Create superadmin user using environment variables
echo "👤 Creating superadmin user..."
echo "📧 Using credentials from env: ${SUPERADMIN_EMAIL:-admin@tm.com}"
php artisan superadmin:create || {
    echo "❌ Failed to create superadmin user!"
    exit 1
}

# Verify user exists in database
echo "🔍 Verifying superadmin exists in database..."
SUPERADMIN_EMAIL_CHECK="${SUPERADMIN_EMAIL:-admin@tm.com}"
php artisan tinker --execute="
\$user = \App\Models\User::where('email', '${SUPERADMIN_EMAIL_CHECK}')->first();
if (\$user) {
    echo '✅ User found: ' . \$user->email . ' (ID: ' . \$user->id . ')' . PHP_EOL;
} else {
    echo '❌ ERROR: User NOT found in database!' . PHP_EOL;
    exit(1);
}
" || {
    echo "❌ User verification failed!"
    exit 1
}

# Cache config and routes
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache

echo "✅ Setup complete!"