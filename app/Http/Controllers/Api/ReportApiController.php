<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class ReportApiController extends Controller
{
    public function workerPerformance(Request $request, $workerId)
    {
        $worker = User::findOrFail($workerId);

        $stats = [
            'worker' => [
                'id' => $worker->id,
                'name' => $worker->name,
                'email' => $worker->email,
            ],
            'total_tasks' => Task::where('assigned_to', $workerId)->count(),
            'completed_tasks' => Task::where('assigned_to', $workerId)->completed()->count(),
            'pending_tasks' => Task::where('assigned_to', $workerId)->pending()->count(),
            'overdue_tasks' => Task::where('assigned_to', $workerId)->overdue()->count(),
        ];

        // Calculate average completion time
        $completedTasks = Task::where('assigned_to', $workerId)
            ->completed()
            ->whereNotNull('completed_at')
            ->get();

        $totalCompletionTime = 0;
        $completionCount = 0;

        foreach ($completedTasks as $task) {
            if ($task->completed_at && $task->created_at) {
                $totalCompletionTime += $task->created_at->diffInHours($task->completed_at);
                $completionCount++;
            }
        }

        $stats['avg_completion_time_hours'] = $completionCount > 0
            ? round($totalCompletionTime / $completionCount, 2)
            : 0;

        return response()->json($stats);
    }
}
