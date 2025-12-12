<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email? : The email address to send to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify email configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? $this->ask('What email address should we send to?');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address!');
            return 1;
        }

        $this->info('Sending test email to: ' . $email);

        try {
            Mail::raw('This is a test email from Task Manager. If you received this, your email configuration is working correctly!', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email from Task Manager');
            });

            $this->info('✓ Email sent successfully!');

            // Check mail driver
            $driver = config('mail.default');
            $this->info('Mail Driver: ' . $driver);

            if ($driver === 'log') {
                $this->warn('Note: MAIL_MAILER is set to "log". Emails are being written to storage/logs/laravel.log instead of being sent.');
                $this->warn('To send real emails, update your .env file with SMTP credentials.');
            } elseif ($driver === 'smtp') {
                $host = config('mail.mailers.smtp.host');
                $this->info('SMTP Host: ' . $host);

                if (empty(config('mail.mailers.smtp.username')) || empty(config('mail.mailers.smtp.password'))) {
                    $this->warn('⚠ Warning: MAIL_USERNAME or MAIL_PASSWORD is empty!');
                    $this->warn('Please add your SMTP credentials to .env file.');
                    $this->info('See EMAIL_SETUP_GUIDE.md for instructions.');
                } else {
                    $this->info('✓ SMTP credentials are configured');
                    $this->info('Check your inbox (or Mailtrap if using test SMTP)');
                }
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('✗ Failed to send email!');
            $this->error('Error: ' . $e->getMessage());

            $this->newLine();
            $this->warn('Troubleshooting tips:');
            $this->warn('1. Check your .env file has correct SMTP credentials');
            $this->warn('2. Run: php artisan config:clear');
            $this->warn('3. See EMAIL_SETUP_GUIDE.md for setup instructions');

            Log::error('Test email failed: ' . $e->getMessage());

            return 1;
        }
    }
}
