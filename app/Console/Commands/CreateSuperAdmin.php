<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateSuperAdmin extends Command
{
    protected $signature = 'superadmin:create';
    protected $description = 'Create or update the superadmin user with hardcoded credentials';

    public function handle()
    {
        $email = 'admin@tm.com';
        $password = 'password@1';

        $this->info('Creating/updating superadmin user...');

        // Delete existing superadmin if exists
        User::where('email', $email)->delete();

        // Create fresh superadmin user
        $user = User::create([
            'name' => 'Super Admin',
            'email' => $email,
            'password' => $password, // Will be auto-hashed by User model
            'role' => 'super_admin',
            'phone' => null,
            'preferred_channel' => 'in_app',
            'email_verified_at' => now(),
        ]);

        $this->info('✅ Superadmin created successfully!');
        $this->info('Email: ' . $email);
        $this->info('Password: ' . $password);
        $this->info('User ID: ' . $user->id);

        return 0;
    }
}