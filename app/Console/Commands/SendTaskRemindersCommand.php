<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskReminder;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendTaskRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for tasks due within 1 hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for tasks due within 1 hour...');

        // Get the time window: now to 1 hour from now
        $now = Carbon::now();
        $oneHourFromNow = $now->copy()->addHour();

        // Find tasks scheduled within the next hour that are not completed or overdue
        // and haven't been reminded yet (only send reminder ONCE per task, ever)
        $upcomingTasks = Task::whereBetween('scheduled_at', [$now, $oneHourFromNow])
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNull('last_reminded_at') // Only tasks that have never been reminded
            ->get();

        if ($upcomingTasks->isEmpty()) {
            $this->info('No tasks due within the next hour that need reminders.');
            return 0;
        }

        $count = 0;
        foreach ($upcomingTasks as $task) {
            // Send reminder to assigned employee
            if ($task->assignedTo) {
                $task->assignedTo->notify(new TaskReminder($task));
            }

            // Send reminder to creator (admin) as well
            if ($task->creator) {
                $task->creator->notify(new TaskReminder($task));
            }

            // Update the last reminded timestamp (only set once)
            $task->update(['last_reminded_at' => $now]);

            $count++;
            $minutesUntilDue = $now->diffInMinutes($task->scheduled_at);
            $this->line("Reminder sent for Task #{$task->id} '{$task->title}' (due in {$minutesUntilDue} minutes).");
        }

        $this->info("Successfully sent {$count} task reminders.");
        return 0;
    }
}
