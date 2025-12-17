<?php
// Simple script to test login functionality
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Login Configuration...\n\n";

echo "APP_ENV: " . env('APP_ENV') . "\n";
echo "APP_URL: " . env('APP_URL') . "\n";
echo "SESSION_DRIVER: " . config('session.driver') . "\n";
echo "SESSION_DOMAIN: " . (config('session.domain') ?: '(empty)') . "\n";
echo "SANCTUM_STATEFUL_DOMAINS: " . env('SANCTUM_STATEFUL_DOMAINS', '(not set)') . "\n\n";

// Test if proxy trust is disabled in local
if (env('APP_ENV') === 'local') {
    echo "✓ Running in LOCAL mode - proxy trust should be DISABLED\n";
} else {
    echo "✓ Running in PRODUCTION mode - proxy trust should be ENABLED\n";
}

echo "\nNow test in browser: http://127.0.0.1:8004/login\n";
echo "Email: superadmin@taskmanager.com\n";
echo "Password: password123\n";
