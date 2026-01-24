<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappChannel
{
    public function send($notifiable, Notification $notification)
    {
        // Handle both regular notifiables (User models) and anonymous notifiables (Notification::route())
        if (method_exists($notifiable, 'routeNotificationFor')) {
            $to = $notifiable->routeNotificationFor('whatsapp', $notification);
        } elseif (isset($notifiable->routes['whatsapp'])) {
            // Anonymous notifiable created with Notification::route()
            $to = $notifiable->routes['whatsapp'];
        } else {
            $to = null;
        }

        if (!$to) {
            return;
        }

        $provider = config('services.whatsapp.provider', 'twilio');

        try {
            $message = $notification->toWhatsapp($notifiable);

            if (!$message) {
                return;
            }

            switch ($provider) {
                case 'meta':
                    $this->sendViaMeta($to, $message);
                    break;

                case 'twilio':
                    $this->sendViaTwilio($to, $message);
                    break;

                case 'vonage':
                    $this->sendViaVonage($to, $message);
                    break;

                case 'ultramsg':
                    $this->sendViaUltramsg($to, $message);
                    break;

                case 'wati':
                    $this->sendViaWati($to, $message);
                    break;

                case 'whapi':
                    $this->sendViaWhapi($to, $message);
                    break;

                default:
                    Log::warning("Unknown WhatsApp provider: {$provider}");
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp send failed: ' . $e->getMessage());
        }
    }

    protected function sendViaMeta($to, $message)
    {
        $token = config('services.whatsapp.meta.token');
        $phoneId = config('services.whatsapp.meta.phone_id');
        $version = config('services.whatsapp.meta.version', 'v21.0');

        if (empty($token) || empty($phoneId)) {
            Log::warning('Meta WhatsApp credentials not configured');
            return;
        }

        // Remove + from phone number if present
        $to = ltrim($to, '+');

        $response = Http::withToken($token)
            ->post("https://graph.facebook.com/{$version}/{$phoneId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'body' => $message,
                ],
            ]);

        if ($response->failed()) {
            Log::error('Meta WhatsApp API error: ' . $response->body());
            throw new \Exception('Meta WhatsApp send failed: ' . $response->body());
        }

        Log::info('Meta WhatsApp message sent', [
            'to' => $to,
            'message_id' => $response->json('messages.0.id'),
        ]);
    }

    protected function sendViaTwilio($to, $message)
    {
        if (!class_exists(\Twilio\Rest\Client::class)) {
            Log::warning('Twilio SDK not installed. Run: composer require twilio/sdk');
            return;
        }

        $client = new \Twilio\Rest\Client(
            config('services.whatsapp.twilio.sid'),
            config('services.whatsapp.twilio.token')
        );

        $client->messages->create(
            'whatsapp:' . $to,
            [
                'from' => config('services.whatsapp.twilio.from'),
                'body' => $message,
            ]
        );
    }

    protected function sendViaVonage($to, $message)
    {
        Http::withBasicAuth(
            config('services.whatsapp.vonage.api_key'),
            config('services.whatsapp.vonage.api_secret')
        )->post('https://messages-sandbox.nexmo.com/v1/messages', [
            'from' => config('services.whatsapp.vonage.from'),
            'to' => $to,
            'message_type' => 'text',
            'text' => $message,
            'channel' => 'whatsapp',
        ]);
    }

    protected function sendViaUltramsg($to, $message)
    {
        $instanceId = config('services.whatsapp.ultramsg.instance_id');
        $token = config('services.whatsapp.ultramsg.token');

        if (empty($instanceId) || empty($token)) {
            Log::warning('Ultramsg credentials not configured');
            return;
        }

        $response = Http::post("https://api.ultramsg.com/{$instanceId}/messages/chat", [
            'token' => $token,
            'to' => $to,
            'body' => $message,
        ]);

        if ($response->failed()) {
            Log::error('Ultramsg API error: ' . $response->body());
            throw new \Exception('Ultramsg send failed: ' . $response->body());
        }

        Log::info('Ultramsg WhatsApp message sent', [
            'to' => $to,
            'response' => $response->json(),
        ]);
    }

    protected function sendViaWati($to, $message)
    {
        Http::withToken(config('services.whatsapp.wati.access_token'))
            ->post(config('services.whatsapp.wati.api_url') . '/api/v1/sendSessionMessage', [
                'whatsappNumber' => $to,
                'message' => $message,
            ]);
    }

    protected function sendViaWhapi($to, $message)
    {
        Http::withToken(config('services.whatsapp.whapi.token'))
            ->post(config('services.whatsapp.whapi.api_url') . '/messages/text', [
                'to' => $to,
                'body' => $message,
            ]);
    }
}
