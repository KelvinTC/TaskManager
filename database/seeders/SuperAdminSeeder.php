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
        $user = User::where('email', $email)->first();

        $resetOnDeploy = filter_var(env('SUPERADMIN_RESET_ON_DEPLOY', false), FILTER_VALIDATE_BOOL);

        if (! $user) {
            $user = User::create([
                'name' => 'Super Admin',
                'email' => $email,
                // The User model uses the 'hashed' cast so pass plain password
                'password' => $password,
                'role' => 'super_admin',
                'phone' => null,
                'preferred_channel' => 'in_app',
                'email_verified_at' => now(),
            ]);

            if (property_exists($this, 'command') && $this->command) {
                $this->command->info('Super Admin created successfully!');
                $this->command->info('Email: ' . $email);
                $this->command->info('Password: ' . $password);
            }
        } else {
            $updates = [
                'name' => 'Super Admin',
                'role' => 'super_admin',
                'preferred_channel' => 'in_app',
            ];

            // Only reset password if explicitly requested or if current password isn't a bcrypt hash
            $current = (string) $user->password;
            $needsHashFix = !preg_match('/^\$2y\$/', $current);
            if ($resetOnDeploy || $needsHashFix) {
                // The User model uses the 'hashed' cast so pass plain password
                $updates['password'] = $password;
            }

            // Only set email_verified_at if it's currently null
            if (is_null($user->email_verified_at)) {
                $updates['email_verified_at'] = now();
            }

            $user->update($updates);

            if (property_exists($this, 'command') && $this->command) {
                $this->command->info('Super Admin updated successfully!');
                $this->command->info('Email: ' . $email);
                if (array_key_exists('password', $updates)) {
                    $this->command->info('Password has been reset via seeder.');
                }
            }
        }
    }
}
