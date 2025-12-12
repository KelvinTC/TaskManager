<?php

namespace App\Notifications;

use App\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TestWhatsappNotification extends Notification
{
    use Queueable;

    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WhatsappChannel::class];
    }

    /**
     * Get the WhatsApp representation of the notification.
     */
    public function toWhatsapp(object $notifiable): string
    {
        return $this->message;
    }
}
