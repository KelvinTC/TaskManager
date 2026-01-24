#!/bin/bash
set -e

echo "🚀 Starting Laravel application..."

# Run setup script
bash /app/.railway/setup.sh

# Start queue worker in background
echo "🔄 Starting queue worker..."
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 &

# Keep the worker running and restart if it crashes
while true; do
    if ! pgrep -f "queue:work" > /dev/null; then
        echo "⚠️  Queue worker stopped, restarting..."
        php artisan queue:work --sleep=3 --tries=3 --max-time=3600 &
    fi
    sleep 10
done