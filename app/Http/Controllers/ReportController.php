<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function workerPerformance(Request $request)
    {
        $user = Auth::user();

        if ($user->isEmployee()) {
            $workerId = $user->id;
        } else {
            $workerId = $request->input('worker_id');
        }

        $worker = User::findOrFail($workerId);

        $stats = [
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

        $stats['avg_completion_time'] = $completionCount > 0
            ? round($totalCompletionTime / $completionCount, 2)
            : 0;

        $workers = User::where('role', 'worker')->get();

        return view('reports.worker-performance', compact('worker', 'stats', 'workers'));
    }

    public function tasksPerWorker()
    {
        $user = Auth::user();

        // Get all employees with their task counts
        $workers = User::where('role', 'employee')
            ->withCount([
                'assignedTasks as total_tasks',
                'assignedTasks as completed_tasks' => function ($query) {
                    $query->where('status', 'completed');
                },
                'assignedTasks as pending_tasks' => function ($query) {
                    $query->where('status', 'pending');
                },
                'assignedTasks as in_progress_tasks' => function ($query) {
                    $query->where('status', 'in_progress');
                },
                'assignedTasks as overdue_tasks' => function ($query) {
                    $query->where('due_date', '<', now())->whereNotIn('status', ['completed']);
                }
            ])
            ->orderBy('total_tasks', 'desc')
            ->get();

        // Calculate totals
        $totals = [
            'total_workers' => $workers->count(),
            'total_tasks' => $workers->sum('total_tasks'),
            'total_completed' => $workers->sum('completed_tasks'),
            'total_pending' => $workers->sum('pending_tasks'),
            'total_in_progress' => $workers->sum('in_progress_tasks'),
            'total_overdue' => $workers->sum('overdue_tasks'),
        ];

        return view('reports.tasks-per-worker', compact('workers', 'totals'));
    }

    public function timeBased(Request $request)
    {
        $period = $request->input('period', 'weekly');
        $user = Auth::user();

        $startDate = match($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            default => now()->startOfWeek(),
        };

        $endDate = now();

        if ($user->canManageTasks()) {
            $tasks = Task::where('created_by', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
        } else {
            $tasks = Task::where('assigned_to', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
        }

        $stats = [
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'completed')->count(),
            'pending_tasks' => $tasks->where('status', 'pending')->count(),
            'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
            'overdue_tasks' => $tasks->where('status', 'overdue')->count(),
        ];

        return view('reports.time-based', compact('stats', 'period', 'startDate', 'endDate'));
    }

    public function export(Request $request)
    {
        // Implement export functionality (CSV, PDF, etc.)
        // This is a placeholder for export logic

        return redirect()->back()->with('info', 'Export functionality coming soon');
    }
}
