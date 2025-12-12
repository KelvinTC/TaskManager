<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return $user->id === $task->assigned_to || $user->id === $task->created_by;
    }

    public function create(User $user): bool
    {
        return $user->canCreateTasks();
    }

    public function update(User $user, Task $task): bool
    {
        // Workers can update status, both client and worker can reschedule
        return $user->id === $task->assigned_to || $user->id === $task->created_by;
    }

    public function delete(User $user, Task $task): bool
    {
        // Only the creator (client) can delete tasks
        return $user->id === $task->created_by;
    }

    public function restore(User $user, Task $task): bool
    {
        return $user->id === $task->created_by;
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $user->id === $task->created_by;
    }
}
