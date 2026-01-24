#!/bin/bash

echo "🚀 Starting application..."

# Start queue worker in background
echo "📨 Starting queue worker..."
php artisan queue:work database --sleep=3 --tries=3 --max-time=3600 &

# Start web server
echo "🌐 Starting web server..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}