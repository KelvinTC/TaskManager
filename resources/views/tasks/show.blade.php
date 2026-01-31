@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-file-text"></i> Task Details</h5>
                        <a href="{{ route('tasks.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h3 class="mb-2">{{ $task->title }}</h3>
                            <div class="d-flex gap-2 mb-3 flex-wrap">
                                @if($task->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($task->status === 'in_progress')
                                    <span class="badge bg-primary">In Progress</span>
                                @else
                                    <span class="badge bg-secondary">Pending</span>
                                @endif

                                @if($task->priority === 'high')
                                    <span class="badge bg-danger">High Priority</span>
                                @elseif($task->priority === 'medium')
                                    <span class="badge bg-warning text-dark">Medium Priority</span>
                                @else
                                    <span class="badge bg-light text-dark">Low Priority</span>
                                @endif

                                @if(($task->visibility ?? 'public') === 'private')
                                    <span class="badge bg-dark"><i class="bi bi-lock-fill"></i> Private</span>
                                @else
                                    <span class="badge bg-light text-dark"><i class="bi bi-globe"></i> Public</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Created By</label>
                            <div>{{ $task->creator->name }}</div>
                            <small class="text-muted">{{ $task->creator->email }}</small>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Assigned To</label>
                            <div>{{ $task->assignedTo->name }}</div>
                            <small class="text-muted">{{ $task->assignedTo->email }}</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Scheduled</label>
                            <div><i class="bi bi-calendar"></i> {{ $task->scheduled_at->format('M d, Y h:i A') }}</div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Created</label>
                            <div><i class="bi bi-calendar-plus"></i> {{ $task->created_at->diffForHumans() }}</div>
                        </div>
                    </div>

                    @if($task->description)
                    <div class="mb-3">
                        <label class="text-muted small">Description</label>
                        <div class="p-2 border rounded bg-light">
                            {!! nl2br(e($task->description)) !!}
                        </div>
                    </div>
                    @endif

                    @if(Auth::user()->isEmployee() && $task->assigned_to === Auth::id())
                        <div class="border rounded p-3 bg-light mb-3">
                            <label class="small mb-2">Update Status</label>
                            <form method="POST" action="{{ route('tasks.update-status', $task) }}" class="d-flex gap-2">
                                @csrf
                                <select name="status" class="form-select form-select-sm" required>
                                    <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">Update</button>
                            </form>
                        </div>
                    @endif

                    @if(Auth::user()->canManageTasks())
                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-secondary btn-sm">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this task?')">
                                    <i class="bi bi-trash"></i> Delete
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
