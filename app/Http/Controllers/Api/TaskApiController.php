<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskRescheduled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->canManageTasks()) {
            $tasks = Task::with(['assignedTo', 'creator'])
                ->where('created_by', $user->id)
                ->latest()
                ->paginate(15);
        } else {
            $tasks = Task::with(['assignedTo', 'creator'])
                ->where('assigned_to', $user->id)
                ->latest()
                ->paginate(15);
        }

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'scheduled_at' => 'required|date',
            'priority' => 'required|in:low,medium,high',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'pending';

        $task = Task::create($validated);

        // Send notification to assigned worker
        $worker = User::find($validated['assigned_to']);
        $worker->notify(new TaskAssigned($task));

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task->load(['assignedTo', 'creator']),
        ], 201);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        $task->load(['assignedTo', 'creator']);

        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'sometimes|required|exists:users,id',
            'scheduled_at' => 'sometimes|required|date',
            'priority' => 'sometimes|required|in:low,medium,high',
            'status' => 'nullable|in:pending,in_progress,completed,overdue',
        ]);

        $oldScheduledAt = $task->scheduled_at;
        $task->update($validated);

        if ($request->status === 'completed' && !$task->completed_at) {
            $task->update(['completed_at' => now()]);
        }

        if ($oldScheduledAt != $task->scheduled_at) {
            $task->creator->notify(new TaskRescheduled($task));
            $task->assignedTo->notify(new TaskRescheduled($task));
        }

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task->load(['assignedTo', 'creator']),
        ]);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }

    public function reschedule(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'scheduled_at' => 'required|date',
        ]);

        $task->update($validated);

        $task->creator->notify(new TaskRescheduled($task));
        $task->assignedTo->notify(new TaskRescheduled($task));

        return response()->json([
            'message' => 'Task rescheduled successfully',
            'task' => $task->load(['assignedTo', 'creator']),
        ]);
    }
}
