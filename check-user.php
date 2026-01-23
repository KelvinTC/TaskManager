#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "🔍 Checking User Database on Railway\n\n";

// Check database connection
try {
    $pdo = DB::connection()->getPdo();
    echo "✅ Database connected\n";
    echo "   DB Path: " . config('database.connections.sqlite.database') . "\n\n";
} catch (\Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Get expected credentials from env
$expectedEmail = env('SUPERADMIN_EMAIL', 'admin@tm.com');
$expectedPassword = env('SUPERADMIN_PASSWORD', 'password@1');

echo "📧 Expected Credentials from ENV:\n";
echo "   Email: $expectedEmail\n";
echo "   Password: $expectedPassword\n\n";

// Check all users
echo "👥 All Users in Database:\n";
$users = User::all();
if ($users->count() === 0) {
    echo "   ⚠️  No users found in database!\n\n";
} else {
    foreach ($users as $user) {
        echo "   - ID: {$user->id}, Email: {$user->email}, Role: {$user->role}\n";
    }
    echo "\n";
}

// Check if expected user exists
$user = User::where('email', $expectedEmail)->first();

if (!$user) {
    echo "❌ User with email '$expectedEmail' NOT found!\n";
    echo "   Run: php artisan superadmin:create\n\n";
    exit(1);
}

echo "✅ User found: {$user->email} (ID: {$user->id})\n";
echo "   Role: {$user->role}\n";
echo "   Password hash: " . substr($user->password, 0, 30) . "...\n\n";

// Test password verification
echo "🔐 Testing Password Verification:\n";

// Check if password is hashed
if (str_starts_with($user->password, '$2y$')) {
    echo "   ✅ Password is bcrypt hashed\n";
} else {
    echo "   ⚠️  Password is NOT bcrypt hashed: " . substr($user->password, 0, 20) . "...\n";
    echo "   This will cause login failures!\n\n";

    // Try to fix it
    echo "🔧 Attempting to fix password hash...\n";
    $user->password = Hash::make($expectedPassword);
    $user->save();
    echo "   ✅ Password rehashed and saved\n\n";

    // Reload user
    $user = User::where('email', $expectedEmail)->first();
}

// Test authentication
if (Hash::check($expectedPassword, $user->password)) {
    echo "   ✅ Password verification PASSED\n";
    echo "   Login should work with: $expectedEmail / $expectedPassword\n\n";
} else {
    echo "   ❌ Password verification FAILED\n";
    echo "   The password '$expectedPassword' does NOT match the stored hash\n\n";

    // Try to fix it
    echo "🔧 Fixing password...\n";
    $user->password = Hash::make($expectedPassword);
    $user->save();
    echo "   ✅ Password updated. Try logging in again.\n\n";
}

echo "✅ Check complete!\n";