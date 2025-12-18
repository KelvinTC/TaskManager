<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\InvitedUser;
use App\Models\Task;
use App\Notifications\UserInvited;
use App\Notifications\TaskAssigned;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;

class TestNotifications extends Command
{
    protected $signature = 'test:notifications {--email=} {--phone=}';
    protected $description = 'Test email and WhatsApp notifications for invitations and task assignments';

    public function handle()
    {
        $email = $this->option('email');
        $phone = $this->option('phone');

        if (!$email && !$phone) {
            $this->error('Please provide at least --email or --phone option');
            $this->info('Usage: php artisan test:notifications --email=test@example.com --phone=+263771234567');
            return 1;
        }

        $this->info('🚀 Testing Notifications...');
        $this->newLine();

        // Test 1: Email Configuration
        $this->testEmailConfig();

        // Test 2: WhatsApp Configuration
        $this->testWhatsAppConfig();

        // Test 3: User Invitation Notification
        if ($email) {
            $this->testUserInvitation($email, $phone);
        }

        // Test 4: Task Assignment Notification
        if ($email) {
            $this->testTaskAssignment($email, $phone);
        }

        $this->newLine();
        $this->info('✅ All tests completed! Check your email and WhatsApp.');

        return 0;
    }

    private function testEmailConfig()
    {
        $this->info('📧 Email Configuration:');
        $this->line('  Mailer: ' . config('mail.default'));
        $this->line('  Host: ' . config('mail.mailers.smtp.host'));
        $this->line('  Port: ' . config('mail.mailers.smtp.port'));
        $this->line('  From: ' . config('mail.from.address'));
        $this->newLine();
    }

    private function testWhatsAppConfig()
    {
        $this->info('📱 WhatsApp Configuration:');
        $this->line('  Provider: ' . config('services.whatsapp.provider'));
        $this->line('  Use Templates: ' . (config('services.whatsapp.use_templates') ? 'Yes' : 'No'));

        if (config('services.whatsapp.provider') === 'meta') {
            $token = config('services.whatsapp.meta.token');
            $phoneId = config('services.whatsapp.meta.phone_id');
            $this->line('  Meta Token: ' . (empty($token) ? '❌ Not set' : '✅ Set (' . substr($token, 0, 20) . '...)'));
            $this->line('  Phone ID: ' . ($phoneId ?? '❌ Not set'));
        }
        $this->newLine();
    }

    private function testUserInvitation($email, $phone)
    {
        $this->info('👤 Testing User Invitation Notification...');

        try {
            // Get admin user to be the inviter
            $admin = User::where('role', 'admin')->first();

            if (!$admin) {
                $this->warn('  No admin user found. Creating mock inviter...');
                $admin = new User([
                    'name' => 'System Admin',
                    'email' => 'admin@taskmanager.com'
                ]);
            }

            // Create a mock invited user
            $invitedUser = new InvitedUser([
                'email' => $email,
                'phone_number' => $phone,
                'role' => 'employee',
            ]);

            // Create a test recipient
            $recipient = new User([
                'email' => $email,
                'phone' => $phone,
                'name' => 'Test User'
            ]);

            // Send notification
            $recipient->notify(new UserInvited($invitedUser, $admin, 'employee'));

            $this->line('  ✅ Invitation notification sent!');
            $this->line('  📧 Email: ' . $email);
            if ($phone) {
                $this->line('  📱 WhatsApp: ' . $phone);
            }
        } catch (\Exception $e) {
            $this->error('  ❌ Failed: ' . $e->getMessage());
            $this->line('  Stack trace: ' . $e->getTraceAsString());
        }

        $this->newLine();
    }

    private function testTaskAssignment($email, $phone)
    {
        $this->info('📋 Testing Task Assignment Notification...');

        try {
            // Create a mock task
            $task = new Task([
                'title' => 'Test Task - Email & WhatsApp Integration',
                'description' => 'This is a test task to verify email and WhatsApp notifications are working correctly.',
                'priority' => 'high',
                'status' => 'pending',
                'scheduled_at' => now()->addHours(2),
            ]);
            $task->id = 999; // Mock ID

            // Create a test recipient
            $recipient = new User([
                'email' => $email,
                'phone' => $phone,
                'name' => 'Test User',
                'preferred_channel' => $phone ? 'whatsapp' : 'email'
            ]);

            // Send notification
            $recipient->notify(new TaskAssigned($task));

            $this->line('  ✅ Task assignment notification sent!');
            $this->line('  📧 Email: ' . $email);
            if ($phone) {
                $this->line('  📱 WhatsApp: ' . $phone);
            }
        } catch (\Exception $e) {
            $this->error('  ❌ Failed: ' . $e->getMessage());
            $this->line('  Stack trace: ' . $e->getTraceAsString());
        }

        $this->newLine();
    }
}
