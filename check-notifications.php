<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 Checking Notification Setup\n\n";

// Check all users
$users = \App\Models\User::all();

echo "📊 Users:\n";
foreach ($users as $user) {
    echo sprintf(
        "  - %s (%s)\n    Phone: %s\n    Channel: %s\n\n",
        $user->name,
        $user->email,
        $user->phone ?: '❌ NOT SET',
        $user->preferred_channel ?: '❌ NOT SET'
    );
}

// Check WhatsApp config
echo "📱 WhatsApp Configuration:\n";
echo "  Provider: " . config('services.whatsapp.provider') . "\n";
echo "  Instance ID: " . (config('services.whatsapp.ultramsg.instance_id') ?: '❌ NOT SET') . "\n";
echo "  Token: " . (config('services.whatsapp.ultramsg.token') ? '✓ SET' : '❌ NOT SET') . "\n\n";

// Check recent tasks
echo "📋 Recent Tasks:\n";
$tasks = \App\Models\Task::with('assignedTo')->latest()->take(5)->get();
foreach ($tasks as $task) {
    echo sprintf(
        "  - %s (assigned to: %s)\n",
        $task->title,
        $task->assignedTo ? $task->assignedTo->name : 'Unassigned'
    );
}

echo "\n✅ Check complete!\n";