<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
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
        $appUrl = config('app.url', 'https://smartwork.up.railway.app');
        $registerUrl = rtrim($appUrl, '/') . '/register?phone=' . urlencode($this->phoneNumber);

        $message = "*Welcome to {$appName}, {$this->userName}!*\n\n" .
                   "You have been invited by *{$this->invitedByName}*\n\n" .
                   "Please complete your registration:\n" .
                   "{$registerUrl}\n\n";

        $provider = config('services.whatsapp.provider', 'ultramsg');

        try {
            switch ($provider) {
                case 'ultramsg':
                    $this->sendViaUltramsg($message);
                    break;
                case 'meta':
                    $this->sendViaMeta($message);
                    break;
                default:
                    Log::warning("Unknown WhatsApp provider: {$provider}");
            }
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp invitation', [
                'phone' => $this->phoneNumber,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function sendViaUltramsg(string $message): void
    {
        $instanceId = config('services.whatsapp.ultramsg.instance_id');
        $token = config('services.whatsapp.ultramsg.token');

        if (empty($instanceId) || empty($token)) {
            Log::warning('Ultramsg credentials not configured');
            return;
        }

        $response = Http::post("https://api.ultramsg.com/{$instanceId}/messages/chat", [
            'token' => $token,
            'to' => $this->phoneNumber,
            'body' => $message,
        ]);

        if ($response->failed()) {
            Log::error('Ultramsg API error: ' . $response->body());
            throw new \Exception('Ultramsg send failed: ' . $response->body());
        }

        Log::info('WhatsApp invitation sent successfully', [
            'phone' => $this->phoneNumber,
            'response' => $response->json(),
        ]);
    }

    protected function sendViaMeta(string $message): void
    {
        $token = config('services.whatsapp.meta.token');
        $phoneId = config('services.whatsapp.meta.phone_id');
        $version = config('services.whatsapp.meta.version', 'v21.0');

        if (empty($token) || empty($phoneId)) {
            Log::warning('Meta WhatsApp credentials not configured');
            return;
        }

        $to = ltrim($this->phoneNumber, '+');

        $response = Http::withToken($token)
            ->post("https://graph.facebook.com/{$version}/{$phoneId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => ['body' => $message],
            ]);

        if ($response->failed()) {
            Log::error('Meta WhatsApp API error: ' . $response->body());
            throw new \Exception('Meta WhatsApp send failed: ' . $response->body());
        }

        Log::info('WhatsApp invitation sent successfully', [
            'phone' => $this->phoneNumber,
            'message_id' => $response->json('messages.0.id'),
        ]);
    }
}
