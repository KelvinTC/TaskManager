<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Idempotent seeding: create once if missing (won't duplicate or fail on redeploy)
        $user = User::firstOrCreate(
            ['email' => 'superadmin@taskmanager.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'phone' => null,
                'preferred_channel' => 'in_app',
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->command->info('Super Admin created successfully!');
            $this->command->info('Email: superadmin@taskmanager.com');
            $this->command->info('Password: password123');
        } else {
            $this->command->info('Super Admin already exists. Skipping creation.');
        }
    }
}
