<?php

namespace App\Notifications;

use App\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInvited extends Notification implements ShouldQueue
{
    use Queueable;

    public $invitedUserEmail;
    public $invitedUserPhone;
    public $invitedByName;
    public $role;

    public function __construct($invitedUser, $invitedBy, $role)
    {
        // Store only the data we need, not the full models
        $this->invitedUserEmail = $invitedUser->email;
        $this->invitedUserPhone = $invitedUser->phone_number;
        $this->invitedByName = $invitedBy->name;
        $this->role = $role;
    }

    public function via($notifiable)
    {
        $channels = [];

        // Only send WhatsApp if phone number is provided
        if (!empty($this->invitedUserPhone)) {
            $channels[] = WhatsappChannel::class;
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        $registerUrl = url('/register?phone=' . urlencode($this->invitedUserPhone));

        return (new MailMessage)
            ->from(
                config('mail.invite.address', config('mail.from.address')),
                config('mail.invite.name', config('mail.from.name'))
            )
            ->subject('You\'ve Been Invited to Task Manager!')
            ->greeting('Hello!')
            ->line('You have been invited to join Task Manager by ' . $this->invitedByName . '.')
            ->line('Role: ' . ucfirst(str_replace('_', ' ', $this->role)))
            ->line('Phone: ' . $this->invitedUserPhone)
            ->action('Register Now', $registerUrl)
            ->line('Please register using your phone number: ' . $this->invitedUserPhone)
            ->line('Thank you for joining our team!');
    }

    public function toWhatsapp($notifiable)
    {
        $appName = config('app.name', 'Task Manager');
        $registerUrl = url('/register?phone=' . urlencode($this->invitedUserPhone));

        return "🎉 *Welcome to {$appName}!*\n\n" .
               "You have been invited by *{$this->invitedByName}*\n\n" .
               "📋 *Role:* " . ucfirst(str_replace('_', ' ', $this->role)) . "\n" .
               "📱 *Phone:* {$this->invitedUserPhone}\n\n" .
               "👉 Please complete your registration:\n" .
               "{$registerUrl}\n\n" .
               "⚠️ Use your phone number *{$this->invitedUserPhone}* when registering.";
    }
}
