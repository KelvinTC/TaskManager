<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client as TwilioClient;

class SmsChannel
{
    protected $client;

    public function __construct()
    {
        $this->client = new TwilioClient(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSms($notifiable);

        if (!$message) {
            return;
        }

        $to = $notifiable->routeNotificationFor('twilio', $notification);

        if (!$to) {
            return;
        }

        $this->client->messages->create(
            $to,
            [
                'from' => config('services.twilio.from'),
                'body' => $message,
            ]
        );
    }
}
