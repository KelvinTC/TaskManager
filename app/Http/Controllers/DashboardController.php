<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return $this->adminDashboard();
        } else {
            return $this->employeeDashboard();
        }
    }

    private function adminDashboard()
    {
        $user = Auth::user();
        $request = request();

        // Build query with filters
        $query = Task::where('created_by', $user->id);

        // Apply status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Apply priority filter
        if ($request->has('priority') && $request->priority != '') {
            $query->where('priority', $request->priority);
        }

        // Apply employee filter
        if ($request->has('assigned_to') && $request->assigned_to != '') {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Apply date range filter
        if ($request->has('date_range') && $request->date_range != '') {
            $dateRange = $request->date_range;
            switch ($dateRange) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        $stats = [
            'total_tasks' => (clone $query)->count(),
            'pending_tasks' => (clone $query)->pending()->count(),
            'in_progress_tasks' => (clone $query)->inProgress()->count(),
            'completed_tasks' => (clone $query)->completed()->count(),
            'overdue_tasks' => (clone $query)->overdue()->count(),
        ];

        $recent_tasks = (clone $query)->with(['assignedTo', 'creator'])
            ->latest()
            ->take(5)
            ->get();

        $employees = User::where('role', 'employee')
            ->withCount(['assignedTasks'])
            ->get();

        $tasksByPriority = (clone $query)
            ->select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->get();

        $tasksByStatus = (clone $query)
            ->whereNotIn('status', ['pending', 'in_progress'])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return view('dashboard.admin', compact(
            'stats',
            'recent_tasks',
            'employees',
            'tasksByPriority',
            'tasksByStatus'
        ));
    }

    private function employeeDashboard()
    {
        $user = Auth::user();

        // Optimize: Get all tasks once and calculate stats in PHP
        $allTasks = Task::where('assigned_to', $user->id)
            ->select('id', 'status', 'priority', 'scheduled_at', 'created_at')
            ->get();

        $stats = [
            'total_tasks' => $allTasks->count(),
            'pending_tasks' => $allTasks->where('status', 'pending')->count(),
            'in_progress_tasks' => $allTasks->where('status', 'in_progress')->count(),
            'completed_tasks' => $allTasks->where('status', 'completed')->count(),
            'overdue_tasks' => $allTasks->filter(function($task) {
                return $task->scheduled_at && $task->scheduled_at->isPast() && $task->status !== 'completed';
            })->count(),
        ];

        $recent_tasks = Task::with(['assignedTo', 'creator'])
            ->where('assigned_to', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $upcoming_tasks = Task::where('assigned_to', $user->id)
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at', 'asc')
            ->take(5)
            ->get();

        $tasksByPriority = $allTasks->groupBy('priority')->map(function($tasks) {
            return (object)['priority' => $tasks->first()->priority, 'count' => $tasks->count()];
        })->values();

        return view('dashboard.employee', compact(
            'stats',
            'recent_tasks',
            'upcoming_tasks',
            'tasksByPriority'
        ));
    }
}
