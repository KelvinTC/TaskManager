<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ManageWorkerEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'worker:email
                            {action? : list|enable|disable|enable-all}
                            {user_id? : User ID to modify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage worker email notification preferences';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action') ?? 'list';

        switch ($action) {
            case 'list':
                $this->listWorkers();
                break;

            case 'enable':
                $userId = $this->argument('user_id');
                if (!$userId) {
                    $this->error('Please provide a user ID: php artisan worker:email enable USER_ID');
                    return 1;
                }
                $this->enableEmail($userId);
                break;

            case 'disable':
                $userId = $this->argument('user_id');
                if (!$userId) {
                    $this->error('Please provide a user ID: php artisan worker:email disable USER_ID');
                    return 1;
                }
                $this->disableEmail($userId);
                break;

            case 'enable-all':
                $this->enableAllWorkers();
                break;

            default:
                $this->error('Invalid action. Use: list, enable, disable, or enable-all');
                return 1;
        }

        return 0;
    }

    private function listWorkers()
    {
        $workers = User::where('role', 'employee')
            ->orderBy('preferred_channel')
            ->get(['id', 'name', 'email', 'preferred_channel']);

        if ($workers->isEmpty()) {
            $this->warn('No workers found in the system.');
            return;
        }

        $this->info('=== Workers and Email Settings ===');
        $this->newLine();

        $headers = ['ID', 'Name', 'Email', 'Channel', 'Email Enabled?'];
        $rows = [];

        foreach ($workers as $worker) {
            $emailEnabled = $worker->preferred_channel === 'email' ? '✓ Yes' : '✗ No';
            $rows[] = [
                $worker->id,
                $worker->name,
                $worker->email,
                $worker->preferred_channel,
                $emailEnabled,
            ];
        }

        $this->table($headers, $rows);

        $emailCount = $workers->where('preferred_channel', 'email')->count();
        $this->newLine();
        $this->info("Total workers: {$workers->count()}");
        $this->info("Email-enabled: {$emailCount}");
        $this->info("Other channels: " . ($workers->count() - $emailCount));

        if ($emailCount === 0) {
            $this->newLine();
            $this->warn('⚠ No workers will receive email notifications!');
            $this->info('Run: php artisan worker:email enable-all');
        }
    }

    private function enableEmail($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return;
        }

        if ($user->role !== 'employee') {
            $this->error("User {$user->name} is not an employee (role: {$user->role})");
            return;
        }

        $oldChannel = $user->preferred_channel;
        $user->preferred_channel = 'email';
        $user->save();

        $this->info("✓ Email notifications enabled for {$user->name}");
        $this->info("  Email: {$user->email}");
        $this->info("  Changed from: {$oldChannel} → email");
    }

    private function disableEmail($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return;
        }

        if ($user->role !== 'employee') {
            $this->error("User {$user->name} is not an employee (role: {$user->role})");
            return;
        }

        $user->preferred_channel = 'in_app';
        $user->save();

        $this->info("✓ Email notifications disabled for {$user->name}");
        $this->info("  Will use: in_app notifications only");
    }

    private function enableAllWorkers()
    {
        if (!$this->confirm('Enable email notifications for ALL workers?')) {
            $this->info('Cancelled.');
            return;
        }

        $workers = User::where('role', 'employee')->get();

        if ($workers->isEmpty()) {
            $this->warn('No workers found in the system.');
            return;
        }

        $updated = 0;
        foreach ($workers as $worker) {
            if ($worker->preferred_channel !== 'email') {
                $worker->preferred_channel = 'email';
                $worker->save();
                $updated++;
            }
        }

        $this->info("✓ Updated {$updated} workers to receive email notifications");
        $this->info("  Total workers: {$workers->count()}");

        $this->newLine();
        $this->listWorkers();
    }
}
