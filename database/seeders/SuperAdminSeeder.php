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
        // Configure via env with sensible defaults
        $email = env('SUPERADMIN_EMAIL', 'superadmin@taskmanager.com');
        $password = env('SUPERADMIN_PASSWORD', 'password123');

        // Idempotent and self-healing: ensure the Super Admin exists and has a known password
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                // The User model uses the 'hashed' cast so passing a plain password is safe,
                // but we also explicitly hash here for clarity and compatibility.
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'phone' => null,
                'preferred_channel' => 'in_app',
                'email_verified_at' => now(),
            ]
        );

        // Output helpful info during seeding
        if (property_exists($this, 'command') && $this->command) {
            if ($user->wasRecentlyCreated) {
                $this->command->info('Super Admin created successfully!');
            } else {
                $this->command->info('Super Admin updated successfully!');
            }
            $this->command->info('Email: ' . $email);
            $this->command->info('Password: ' . $password);
        }
    }
}
