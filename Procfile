web: bash start.sh
worker: php artisan queue:work --verbose --tries=3 --timeout=90
scheduler: php artisan schedule:work