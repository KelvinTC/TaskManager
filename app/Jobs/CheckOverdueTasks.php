<?php

namespace App\Jobs;

use App\Models\Task;
use App\Notifications\TaskOverdue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckOverdueTasks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        Log::info('Checking for overdue tasks...');

        $overdueTasks = Task::where('scheduled_at', '<', now())
            ->whereNotIn('status', ['completed', 'overdue'])
            ->get();

        foreach ($overdueTasks as $task) {
            // Mark task as overdue
            $task->update(['status' => 'overdue']);

            // Notify both worker and client
            $task->assignedTo->notify(new TaskOverdue($task));
            $task->creator->notify(new TaskOverdue($task));

            Log::info("Task #{$task->id} marked as overdue and notifications sent.");
        }

        Log::info("Overdue tasks check completed. Found {$overdueTasks->count()} overdue tasks.");
    }
}
