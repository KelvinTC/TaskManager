#!/bin/bash

echo "Starting Queue Worker for Email Notifications..."
echo "This will process all queued emails in the background."
echo ""
echo "Press Ctrl+C to stop the queue worker."
echo ""

# Run queue worker
php artisan queue:work --verbose --tries=3 --timeout=90
