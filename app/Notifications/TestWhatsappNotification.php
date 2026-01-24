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
    public function __construct($message = null)
    {
        $this->message = $message ?? "🎉 *Test WhatsApp Notification*\n\nThis is a test message from your Task Manager application.\n\nIf you received this, your WhatsApp notifications are working correctly! ✅";
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
