#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Notifications\TestWhatsappNotification;
use Illuminate\Support\Facades\Notification;

echo "🔍 WhatsApp Notification Test\n";
echo "===============================\n\n";

// Check configuration
echo "📋 Configuration:\n";
echo "   Provider: " . config('services.whatsapp.provider') . "\n";cluac;
echo "   Meta Token: " . (config('services.whatsapp.meta.token') ? '✅ Set' : '❌ Not set') . "\n";
echo "   Meta Phone ID: " . (config('services.whatsapp.meta.phone_id') ? '✅ ' . config('services.whatsapp.meta.phone_id') : '❌ Not set') . "\n";
echo "   Use Templates: " . (config('services.whatsapp.use_templates') ? 'Yes' : 'No') . "\n\n";

// Get user to test with
echo "👤 Finding a user with phone number...\n";
$user = User::whereNotNull('phone')->first();

if (!$user) {
    echo "❌ No users found with phone numbers!\n";
    echo "   Please add a phone number to a user first.\n\n";
    exit(1);
}

echo "   Found: {$user->name} ({$user->email})\n";
echo "   Phone: {$user->phone}\n";
echo "   Preferred Channel: {$user->preferred_channel}\n\n";

// Ask for confirmation
echo "📱 Send test WhatsApp message to {$user->phone}? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$answer = trim($line);
fclose($handle);

if (strtolower($answer) !== 'yes' && strtolower($answer) !== 'y') {
    echo "❌ Test cancelled.\n";
    exit(0);
}

echo "\n📤 Sending WhatsApp notification...\n";

try {
    // Send notification
    $user->notify(new TestWhatsappNotification());

    echo "✅ Notification queued successfully!\n\n";
    echo "📝 Next steps:\n";
    echo "   1. Check your WhatsApp for the test message\n";
    echo "   2. Check logs: tail -f storage/logs/laravel.log\n";
    echo "   3. If using queues, run: php artisan queue:work\n\n";

} catch (\Exception $e) {
    echo "❌ Error sending notification:\n";
    echo "   " . $e->getMessage() . "\n\n";
    exit(1);
}
