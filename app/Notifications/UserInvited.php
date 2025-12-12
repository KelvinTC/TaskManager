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

    protected $invitedUser;
    protected $invitedBy;
    protected $role;

    public function __construct($invitedUser, $invitedBy, $role)
    {
        $this->invitedUser = $invitedUser;
        $this->invitedBy = $invitedBy;
        $this->role = $role;
    }

    public function via($notifiable)
    {
        $channels = ['mail'];

        // Add WhatsApp if phone number is provided
        if (!empty($this->invitedUser->phone_number)) {
            $channels[] = WhatsappChannel::class;
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->from(
                config('mail.invite.address', config('mail.from.address')),
                config('mail.invite.name', config('mail.from.name'))
            )
            ->subject('You\'ve Been Invited to Task Manager!')
            ->greeting('Hello!')
            ->line('You have been invited to join Task Manager by ' . $this->invitedBy->name . '.')
            ->line('Role: ' . ucfirst(str_replace('_', ' ', $this->role)))
            ->line('Email: ' . $this->invitedUser->email)
            ->action('Register Now', url('/register'))
            ->line('Please register with the email address: ' . $this->invitedUser->email)
            ->line('Thank you for joining our team!');
    }

    public function toWhatsapp($notifiable)
    {
        $appName = config('app.name', 'Task Manager');
        $registerUrl = url('/register');

        return "🎉 *Welcome to {$appName}!*\n\n" .
               "You have been invited by *{$this->invitedBy->name}*\n\n" .
               "📋 *Role:* " . ucfirst(str_replace('_', ' ', $this->role)) . "\n" .
               "📧 *Email:* {$this->invitedUser->email}\n\n" .
               "👉 Please complete your registration:\n" .
               "{$registerUrl}\n\n" .
               "⚠️ *Important:* Use the email address *{$this->invitedUser->email}* when registering.\n\n" .
               "Welcome to the team! 🚀";
    }

    public function useWhatsappTemplate()
    {
        return config('services.whatsapp.use_templates', false) &&
               config('services.whatsapp.provider') === 'meta';
    }

    public function toWhatsappTemplate($notifiable)
    {
        return [
            'name' => config('services.whatsapp.templates.user_invitation', 'hello_world'),
            'language' => 'en_US',
            // If your template has variables, add them here:
            // 'components' => [
            //     [
            //         'type' => 'body',
            //         'parameters' => [
            //             ['type' => 'text', 'text' => $this->invitedBy->name],
            //             ['type' => 'text', 'text' => $this->role],
            //             ['type' => 'text', 'text' => $this->invitedUser->email],
            //         ],
            //     ],
            // ],
        ];
    }
}
