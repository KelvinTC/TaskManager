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
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@taskmanager.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'phone' => null,
            'preferred_channel' => 'in_app',
        ]);

        $this->command->info('Super Admin created successfully!');
        $this->command->info('Email: superadmin@taskmanager.com');
        $this->command->info('Password: password123');
    }
}
