<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = DB::table('users')->select('id', 'name', 'email', 'phone', 'preferred_channel')->get();

echo "Users in database:\n";
echo str_repeat('-', 80) . "\n";

foreach ($users as $user) {
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Phone: {$user->phone}\n";
    echo "Preferred Channel: {$user->preferred_channel}\n";
    echo str_repeat('-', 80) . "\n";
}
