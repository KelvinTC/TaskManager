<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Channels\WhatsappChannel;
use Illuminate\Support\Facades\Notification;

class TestWhatsapp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test {phone? : Phone number with country code (e.g., +263771234567)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test WhatsApp notification configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->argument('phone') ?? $this->ask('Enter phone number (with country code, e.g., +263771234567)');

        // Validate phone number format
        if (!preg_match('/^\+\d{10,15}$/', $phone)) {
            $this->error('Invalid phone number format!');
            $this->warn('Phone must start with + and include country code');
            $this->warn('Example: +263771234567');
            return 1;
        }

        $this->info('Testing WhatsApp configuration...');
        $this->newLine();

        // Check provider
        $provider = config('services.whatsapp.provider');
        $this->info("Provider: {$provider}");

        // Check credentials
        $providerConfig = config("services.whatsapp.{$provider}");

        if (empty($providerConfig)) {
            $this->error("No configuration found for provider: {$provider}");
            $this->warn('Please check your .env file and config/services.php');
            return 1;
        }

        $this->info('Configuration found ✓');
        $this->newLine();

        // Display provider-specific info
        switch ($provider) {
            case 'meta':
                $hasToken = !empty(config('services.whatsapp.meta.token'));
                $phoneId = config('services.whatsapp.meta.phone_id');
                $this->info("Access Token: " . ($hasToken ? '✓ Set' : '❌ Not set'));
                $this->info("Phone Number ID: " . ($phoneId ?: '❌ Not set'));
                $this->info("API Version: " . config('services.whatsapp.meta.version'));
                if ($hasToken && $phoneId) {
                    $this->info("✓ Meta Cloud API configured!");
                    $this->info("FREE: 1,000 conversations/month");
                }
                break;

            case 'ultramsg':
                $instanceId = config('services.whatsapp.ultramsg.instance_id');
                $hasToken = !empty(config('services.whatsapp.ultramsg.token'));
                $this->info("Instance ID: " . ($instanceId ?: '❌ Not set'));
                $this->info("Token: " . ($hasToken ? '✓ Set' : '❌ Not set'));
                break;

            case 'twilio':
                $hasSid = !empty(config('services.whatsapp.twilio.sid'));
                $hasToken = !empty(config('services.whatsapp.twilio.token'));
                $this->info("SID: " . ($hasSid ? '✓ Set' : '❌ Not set'));
                $this->info("Token: " . ($hasToken ? '✓ Set' : '❌ Not set'));
                $this->info("From: " . config('services.whatsapp.twilio.from'));
                break;

            case 'whapi':
                $apiUrl = config('services.whatsapp.whapi.api_url');
                $hasToken = !empty(config('services.whatsapp.whapi.token'));
                $this->info("API URL: " . ($apiUrl ?: '❌ Not set'));
                $this->info("Token: " . ($hasToken ? '✓ Set' : '❌ Not set'));
                break;

            case 'wati':
                $apiUrl = config('services.whatsapp.wati.api_url');
                $hasToken = !empty(config('services.whatsapp.wati.access_token'));
                $this->info("API URL: " . ($apiUrl ?: '❌ Not set'));
                $this->info("Access Token: " . ($hasToken ? '✓ Set' : '❌ Not set'));
                break;

            case 'vonage':
                $hasKey = !empty(config('services.whatsapp.vonage.api_key'));
                $hasSecret = !empty(config('services.whatsapp.vonage.api_secret'));
                $this->info("API Key: " . ($hasKey ? '✓ Set' : '❌ Not set'));
                $this->info("API Secret: " . ($hasSecret ? '✓ Set' : '❌ Not set'));
                break;
        }

        $this->newLine();

        // Send test message
        if (!$this->confirm('Send test WhatsApp message to ' . $phone . '?', true)) {
            $this->info('Test cancelled.');
            return 0;
        }

        $this->info('Sending test message...');

        try {
            $testMessage = "Test Message from Task Manager\n\n" .
                          "This is a test WhatsApp notification.\n" .
                          "If you received this, your WhatsApp configuration is working correctly!\n\n" .
                          "Provider: {$provider}\n" .
                          "Sent at: " . now()->format('Y-m-d H:i:s');

            // Create a test notification
            Notification::route('whatsapp', $phone)
                ->notify(new \App\Notifications\TestWhatsappNotification($testMessage));

            $this->newLine();
            $this->info('✓ Message sent successfully!');
            $this->info('Check WhatsApp on: ' . $phone);
            $this->newLine();
            $this->warn('Note: Message may take a few seconds to arrive.');

            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('✗ Failed to send WhatsApp message!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Troubleshooting tips:');
            $this->warn('1. Check your .env file has correct credentials');
            $this->warn('2. Verify your WhatsApp provider account is active');
            $this->warn('3. Check phone number format (+263771234567)');
            $this->warn('4. See WHATSAPP_SETUP_GUIDE.md for setup instructions');
            $this->warn('5. Check storage/logs/laravel.log for details');

            return 1;
        }
    }
}
