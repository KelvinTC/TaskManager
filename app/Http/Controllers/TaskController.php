<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskRescheduled;
use App\Notifications\TaskStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        $status = $request->get('status');
        $priority = $request->get('priority');

        // Validate per_page
        if (!in_array($perPage, [5, 10, 50])) {
            $perPage = 10;
        }

        if ($user->canManageTasks()) {
            // Admins/Super admins see tasks they created + public tasks assigned to others
            $query = Task::with(['assignedTo', 'creator'])
                ->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('assigned_to', $user->id)
                      ->orWhere(function($query) use ($user) {
                          $query->where('visibility', 'public');
                          // Super admins can see all tasks
                          if (!$user->isSuperAdmin()) {
                              // Regular admins only see public tasks they didn't create
                              $query->where('created_by', '!=', $user->id);
                          }
                      });
                });
        } else {
            // Employees see tasks assigned to them + public tasks
            $query = Task::with(['assignedTo', 'creator'])
                ->where(function($q) use ($user) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere('visibility', 'public');
                });
        }

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('assignedTo', function($query) use ($search) {
                      $query->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('creator', function($query) use ($search) {
                      $query->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Apply status filter
        if ($status) {
            if ($status === 'overdue') {
                // Overdue = past scheduled date and not completed
                $query->where('scheduled_at', '<', now())
                      ->where('status', '!=', 'completed');
            } else {
                $query->where('status', $status);
            }
        }

        // Apply priority filter
        if ($priority) {
            $query->where('priority', $priority);
        }

        $tasks = $query->latest()->paginate($perPage)->appends([
            'per_page' => $perPage,
            'search' => $search,
            'status' => $status,
            'priority' => $priority
        ]);

        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $user = Auth::user();

        // Super admins can assign tasks to both admins and employees
        if ($user->isSuperAdmin()) {
            $workers = User::whereIn('role', ['admin', 'employee'])->get();
        } else {
            // Regular admins can only assign to employees
            $workers = User::where('role', 'employee')->get();
        }

        return view('tasks.create', compact('workers'));
    }

    public function store(Request $request)
    {
        \Log::info('TaskController store method called', [
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'scheduled_at' => 'required|date',
            'priority' => 'required|in:low,medium,high',
            'visibility' => 'required|in:public,private',
        ]);

        \Log::info('Validation passed', ['validated_data' => $validated]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'pending';

        $task = Task::create($validated);

        \Log::info('Task created successfully', ['task_id' => $task->id]);

        // Send notification to assigned worker
        $worker = User::find($validated['assigned_to']);
        $worker->notify(new TaskAssigned($task));

        return redirect()->route('tasks.index')
            ->with('success', 'Task created and assigned successfully.');
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        $task->load(['assignedTo', 'creator']);

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        $user = Auth::user();

        // Super admins can assign tasks to both admins and employees
        if ($user->isSuperAdmin()) {
            $workers = User::whereIn('role', ['admin', 'employee'])->get();
        } else {
            // Regular admins can only assign to employees
            $workers = User::where('role', 'employee')->get();
        }

        return view('tasks.edit', compact('task', 'workers'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'scheduled_at' => 'required|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'nullable|in:pending,in_progress,completed,overdue',
            'visibility' => 'required|in:public,private',
        ]);

        $oldScheduledAt = $task->scheduled_at;
        $task->update($validated);

        // If task completed, set completed_at
        if ($request->status === 'completed' && !$task->completed_at) {
            $task->update(['completed_at' => now()]);
        }

        // If rescheduled, notify both client and worker
        if ($oldScheduledAt != $task->scheduled_at) {
            $task->creator->notify(new TaskRescheduled($task));
            $task->assignedTo->notify(new TaskRescheduled($task));
        }

        return redirect()->route('tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task->update($validated);

        if ($request->status === 'completed') {
            $task->update(['completed_at' => now()]);
        }

        // Notify creator of status change
        $task->creator->notify(new TaskStatusUpdated($task));

        return redirect()->back()
            ->with('success', 'Task status updated successfully.');
    }

    public function reschedule(Request $request, Task $task)
    {
        $validated = $request->validate([
            'scheduled_at' => 'required|date',
        ]);

        $task->update($validated);

        // Notify both parties
        $task->creator->notify(new TaskRescheduled($task));
        $task->assignedTo->notify(new TaskRescheduled($task));

        return response()->json([
            'success' => true,
            'message' => 'Task rescheduled successfully.',
        ]);
    }

    public function calendar()
    {
        return view('tasks.calendar');
    }

    public function json()
    {
        $user = Auth::user();

        if ($user->canManageTasks()) {
            $tasks = Task::where('created_by', $user->id)->get();
        } else {
            $tasks = Task::where('assigned_to', $user->id)->get();
        }

        $events = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->scheduled_at->toIso8601String(),
                'color' => $this->getColorByStatus($task->status),
                'extendedProps' => [
                    'status' => $task->status,
                    'priority' => $task->priority,
                ],
            ];
        });

        return response()->json($events);
    }

    private function getColorByStatus($status)
    {
        return match($status) {
            'pending' => '#ffc107',
            'in_progress' => '#17a2b8',
            'completed' => '#28a745',
            'overdue' => '#dc3545',
            default => '#6c757d',
        };
    }
}
