@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="bi bi-list-task"></i> My Tasks</h2>
                @if(Auth::user()->canCreateTasks())
                    <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle"></i> Create New Task
                    </a>
                @endif
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Title</th>
                                    <th>Assigned To</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Scheduled</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tasks as $task)
                                    <tr>
                                        <td>
                                            <strong>{{ $task->title }}</strong>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                        </td>
                                        <td>
                                            @if($task->assignedTo)
                                                <span class="badge bg-info">{{ $task->assignedTo->name }}</span>
                                            @else
                                                <span class="badge bg-secondary">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'primary' : 'warning') }}">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $task->scheduled_at ? $task->scheduled_at->format('M d, Y H:i') : 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @if(Auth::user()->canManageTasks())
                                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2">No tasks found.</p>
                                            @if(Auth::user()->canCreateTasks())
                                                <a href="{{ route('tasks.create') }}" class="btn btn-primary">Create Your First Task</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $tasks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
