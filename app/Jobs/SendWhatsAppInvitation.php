<?php

namespace App\Jobs;

use App\Channels\WhatsappChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendWhatsAppInvitation implements ShouldQueue
{
    use Queueable;

    public $phoneNumber;
    public $userName;
    public $invitedByName;
    public $role;

    /**
     * Create a new job instance.
     */
    public function __construct(string $phoneNumber, string $userName, string $invitedByName, string $role)
    {
        $this->phoneNumber = $phoneNumber;
        $this->userName = $userName;
        $this->invitedByName = $invitedByName;
        $this->role = $role;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $appName = config('app.name', 'Task Manager');
        $registerUrl = url('/register?phone=' . urlencode($this->phoneNumber));

        $message = "*Welcome to {$appName}, {$this->userName}!*\n\n" .
                   "You have been invited by *{$this->invitedByName}*\n\n" .
                   "Please complete your registration:\n" .
                   "{$registerUrl}\n\n";

        $channel = new WhatsappChannel();

        // Create a simple object to pass to the channel
        $notifiable = new class($this->phoneNumber) {
            public $phone;
            public function __construct($phone) { $this->phone = $phone; }
            public function routeNotificationFor($channel) { return $this->phone; }
        };

        // Create a simple notification object
        $notification = new class($message) {
            public $message;
            public function __construct($message) { $this->message = $message; }
            public function toWhatsapp($notifiable) { return $this->message; }
        };

        try {
            $channel->send($notifiable, $notification);
            Log::info('WhatsApp invitation sent successfully', ['phone' => $this->phoneNumber]);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp invitation', [
                'phone' => $this->phoneNumber,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
