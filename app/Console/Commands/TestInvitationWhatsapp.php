<?php

namespace App\Console\Commands;

use App\Models\InvitedUser;
use App\Notifications\UserInvited;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class TestInvitationWhatsapp extends Command
{
    protected $signature = 'test:invite-whatsapp {phone : Phone number with country code}';
    protected $description = 'Test WhatsApp invitation notification';

    public function handle()
    {
        $phone = $this->argument('phone');

        if (!preg_match('/^\+\d{10,15}$/', $phone)) {
            $this->error('Invalid phone number format!');
            $this->warn('Phone must start with + and include country code');
            $this->warn('Example: +263783017279');
            return 1;
        }

        $this->info('Testing WhatsApp invitation...');

        // Create a test invited user object (not saved to DB)
        $testInvite = new InvitedUser([
            'email' => 'test@example.com',
            'phone_number' => $phone,
            'role' => 'employee',
        ]);

        // Mock inviter
        $inviter = (object) ['name' => 'Test Admin'];

        try {
            Notification::route('whatsapp', $phone)
                ->notify(new UserInvited($testInvite, $inviter, 'employee'));

            $this->newLine();
            $this->info('✓ WhatsApp invitation sent successfully!');
            $this->info('Check WhatsApp on: ' . $phone);
            
            return 0;
        } catch (\Exception $e) {
            $this->error('✗ Failed to send WhatsApp invitation!');
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
