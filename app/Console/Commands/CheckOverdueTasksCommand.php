<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskOverdue;
use Illuminate\Console\Command;

class CheckOverdueTasksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for overdue tasks and update their status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for overdue tasks...');

        // Find tasks that are past their scheduled time and not completed
        $overdueTasks = Task::where('scheduled_at', '<', now())
            ->whereNotIn('status', ['completed', 'overdue'])
            ->get();

        if ($overdueTasks->isEmpty()) {
            $this->info('No overdue tasks found.');
            return 0;
        }

        $count = 0;
        foreach ($overdueTasks as $task) {
            // Mark task as overdue
            $task->update(['status' => 'overdue']);

            // Only send notifications if not already sent
            if (!$task->overdue_notified_at) {
                // Notify assigned employee
                if ($task->assignedTo) {
                    $task->assignedTo->notify(new TaskOverdue($task));
                }

                // Notify creator (admin)
                if ($task->creator) {
                    $task->creator->notify(new TaskOverdue($task));
                }

                // Mark as notified
                $task->update(['overdue_notified_at' => now()]);

                $this->line("Task #{$task->id} '{$task->title}' marked as overdue and notifications sent.");
            } else {
                $this->line("Task #{$task->id} '{$task->title}' already marked as overdue (notification already sent).");
            }

            $count++;
        }

        $this->info("Successfully processed {$count} overdue tasks.");
        return 0;
    }
}
