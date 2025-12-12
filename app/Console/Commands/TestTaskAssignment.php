<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Console\Command;

class TestTaskAssignment extends Command
{
    protected $signature = 'test:task-assignment {user_id} {--text : Use text message instead of template}';
    protected $description = 'Test task assignment notification';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $useText = $this->option('text');

        $user = User::find($userId);

        if (!$user) {
            $this->error("User not found with ID: {$userId}");
            return 1;
        }

        if (!$user->phone) {
            $this->error("User does not have a phone number configured.");
            return 1;
        }

        // Get or create a test task
        $task = Task::where('assigned_to', $userId)->first();

        if (!$task) {
            $this->info('No existing task found. Creating a test task...');
            $task = Task::create([
                'title' => 'Test Task for WhatsApp',
                'description' => 'This is a test task to verify WhatsApp notifications',
                'priority' => 'high',
                'status' => 'pending',
                'scheduled_at' => now()->addHours(2),
                'assigned_to' => $userId,
                'created_by' => 1,
            ]);
            $this->info("Test task created with ID: {$task->id}");
        }

        $this->info("Testing task assignment notification...");
        $this->info("User: {$user->name} ({$user->email})");
        $this->info("Phone: {$user->phone}");
        $this->info("Task: {$task->title}");
        $this->info("Preferred Channel: {$user->preferred_channel}");
        $this->info("WhatsApp Provider: " . config('services.whatsapp.provider'));
        $this->info("Use Templates: " . (config('services.whatsapp.use_templates') ? 'Yes' : 'No'));

        if ($useText) {
            $this->info("Forcing text message mode...");
            config(['services.whatsapp.use_templates' => false]);
        }

        try {
            $user->notify(new TaskAssigned($task));
            $this->info('✓ Notification queued successfully!');
            $this->info('Check the queue worker logs and storage/logs/laravel.log for details.');

            return 0;
        } catch (\Exception $e) {
            $this->error('✗ Notification failed: ' . $e->getMessage());
            return 1;
        }
    }
}
