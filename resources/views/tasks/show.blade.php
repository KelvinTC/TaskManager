@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg">
                <div class="card-header bg-dark text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-file-text"></i> Task Details</h4>
                        <a href="{{ route('tasks.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h2 class="mb-3">{{ $task->title }}</h2>
                            <div class="d-flex gap-2 mb-3">
                                <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'primary' : 'warning') }} fs-6">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                                <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'secondary') }} fs-6">
                                    Priority: {{ ucfirst($task->priority) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-muted">Created By</h6>
                                    <p class="mb-0"><strong>{{ $task->creator->name }}</strong></p>
                                    <small class="text-muted">{{ $task->creator->email }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-muted">Assigned To</h6>
                                    <p class="mb-0"><strong>{{ $task->assignedTo->name }}</strong></p>
                                    <small class="text-muted">{{ $task->assignedTo->email }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Scheduled Date & Time</h6>
                            <p><i class="bi bi-calendar"></i> {{ $task->scheduled_at->format('F d, Y') }}</p>
                            <p><i class="bi bi-clock"></i> {{ $task->scheduled_at->format('h:i A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Created</h6>
                            <p><i class="bi bi-calendar-plus"></i> {{ $task->created_at->format('F d, Y h:i A') }}</p>
                            <small class="text-muted">{{ $task->created_at->diffForHumans() }}</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted">Description</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                {!! nl2br(e($task->description)) !!}
                            </div>
                        </div>
                    </div>

                    @if(Auth::user()->isEmployee() && $task->assigned_to === Auth::id())
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Update Task Status</h6>
                                <form method="POST" action="{{ route('tasks.update-status', $task) }}" class="d-flex gap-2">
                                    @csrf
                                    <select name="status" class="form-select" required>
                                        <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                    <button type="submit" class="btn btn-light">Update</button>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if(Auth::user()->canManageTasks())
                        <div class="d-flex gap-2 mt-4">
                            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Edit Task
                            </a>
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this task?')">
                                    <i class="bi bi-trash"></i> Delete Task
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
