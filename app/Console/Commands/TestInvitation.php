<?php

namespace App\Console\Commands;

use App\Models\InvitedUser;
use App\Models\User;
use App\Notifications\UserInvited;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class TestInvitation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:invitation {email} {phone?} {--role=employee}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test user invitation with optional WhatsApp notification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $phone = $this->argument('phone');
        $role = $this->option('role');

        // Get the first super admin or admin user
        $inviter = User::where('role', 'super_admin')
            ->orWhere('role', 'admin')
            ->first();

        if (!$inviter) {
            $this->error('No admin user found to send invitation from');
            return 1;
        }

        // Create a mock invited user
        $invitedUser = new InvitedUser([
            'email' => $email,
            'phone_number' => $phone,
            'role' => $role,
            'invited_by' => $inviter->id,
        ]);

        $this->info('Sending invitation to:');
        $this->info('Email: ' . $email);
        if ($phone) {
            $this->info('Phone: ' . $phone);
            $this->info('WhatsApp notification will be sent!');
        } else {
            $this->info('No phone number - only email will be sent');
        }
        $this->info('Role: ' . $role);
        $this->info('Invited by: ' . $inviter->name);

        try {
            $notification = Notification::route('mail', $email);

            if ($phone) {
                $notification = $notification->route('whatsapp', $phone);
            }

            $notification->notify(new UserInvited($invitedUser, $inviter, $role));

            $this->info('Invitation notification queued successfully!');
            $this->info('Check your queue worker logs and email/WhatsApp to verify delivery.');

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to send invitation: ' . $e->getMessage());
            return 1;
        }
    }
}
