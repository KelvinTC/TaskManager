<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckWhatsAppToken extends Command
{
    protected $signature = 'whatsapp:check-token';
    protected $description = 'Check if WhatsApp access token is valid';

    public function handle()
    {
        $token = config('services.whatsapp.meta.token');
        $phoneId = config('services.whatsapp.meta.phone_id');

        if (empty($token) || empty($phoneId)) {
            $this->error('WhatsApp credentials not configured');
            return 1;
        }

        // Test the token by making a simple API call
        $response = Http::withToken($token)
            ->get("https://graph.facebook.com/v21.0/{$phoneId}");

        if ($response->successful()) {
            $this->info('✓ WhatsApp token is valid');
            $this->info('Phone Number ID: ' . $phoneId);

            $data = $response->json();
            if (isset($data['verified_name'])) {
                $this->info('Business Name: ' . $data['verified_name']);
            }

            return 0;
        } else {
            $error = $response->json();

            if (isset($error['error']['code']) && $error['error']['code'] == 190) {
                $this->error('✗ Token has EXPIRED!');
                $this->error('Error: ' . $error['error']['message']);
                $this->warn('');
                $this->warn('ACTION REQUIRED:');
                $this->warn('1. Go to https://developers.facebook.com/apps/');
                $this->warn('2. Select your WhatsApp App');
                $this->warn('3. Generate a new System User token (permanent)');
                $this->warn('4. Update META_WHATSAPP_TOKEN in your .env file');
                $this->warn('5. Run: php artisan config:clear');

                Log::error('WhatsApp token expired', $error);
            } else {
                $this->error('✗ Token validation failed');
                $this->error(json_encode($error, JSON_PRETTY_PRINT));
            }

            return 1;
        }
    }
}
