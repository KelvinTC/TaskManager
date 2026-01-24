#!/bin/bash

echo "🚀 Starting application..."

# Function to restart queue worker if it dies
start_queue_worker() {
    while true; do
        echo "📨 Starting queue worker..."
        php artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --verbose
        echo "⚠️  Queue worker stopped. Restarting in 5 seconds..."
        sleep 5
    done
}

# Start queue worker in background with auto-restart
start_queue_worker &

# Start web server
echo "🌐 Starting web server..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}