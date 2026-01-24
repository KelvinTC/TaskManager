@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-12 col-md-8 mb-3 mb-md-0">
                <h2 class="mb-0">
                    <i class="bi bi-person-badge"></i>
                    <span class="d-none d-sm-inline">Welcome, {{ Auth::user()->name }}</span>
                    <span class="d-inline d-sm-none">Welcome</span>
                </h2>
                <p class="text-muted mb-0 mt-2 d-none d-sm-block">Track your assigned tasks and performance.</p>
            </div>
            <div class="col-12 col-md-4 text-md-end">
                <a href="{{ route('tasks.index') }}" class="btn btn-primary btn-lg w-100 w-md-auto">
                    <i class="bi bi-list-task"></i> View All Tasks
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="card text-center shadow-sm h-100">
                <div class="card-body">
                    <i class="bi bi-list-check text-primary" style="font-size: 2rem;"></i>
                    <h3 class="text-primary mb-0 mt-2">{{ $stats['total_tasks'] }}</h3>
                    <p class="mb-0 small text-muted">Total</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card text-center shadow-sm h-100">
                <div class="card-body">
                    <i class="bi bi-arrow-repeat text-info" style="font-size: 2rem;"></i>
                    <h3 class="text-info mb-0 mt-2">{{ $stats['in_progress_tasks'] }}</h3>
                    <p class="mb-0 small text-muted">Active</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card text-center shadow-sm h-100">
                <div class="card-body">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <h3 class="text-success mb-0 mt-2">{{ $stats['completed_tasks'] }}</h3>
                    <p class="mb-0 small text-muted">Done</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card text-center shadow-sm h-100">
                <div class="card-body">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
                    <h3 class="text-danger mb-0 mt-2">{{ $stats['overdue_tasks'] }}</h3>
                    <p class="mb-0 small text-muted">Overdue</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Tasks -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock"></i> Recent Tasks</h5>
                </div>
                <div class="card-body">
                    @if($recent_tasks->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recent_tasks as $task)
                                <a href="{{ route('tasks.show', $task) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <i class="bi bi-file-text"></i> {{ $task->title }}
                                            </h6>
                                            <p class="mb-2 text-muted small">{{ Str::limit($task->description, 80) }}</p>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'info' : 'warning') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                </span>
                                                <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'secondary') }}">
                                                    @if($task->priority === 'high') 🔴 @elseif($task->priority === 'medium') 🟡 @else 🟢 @endif
                                                    {{ ucfirst($task->priority) }}
                                                </span>
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-calendar-event"></i> {{ $task->scheduled_at->format('M d, Y') }}
                                                </span>
                                            </div>
                                        </div>
                                        <small class="text-muted ms-2">{{ $task->created_at->diffForHumans() }}</small>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-3">No recent tasks assigned to you.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Tasks -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar3"></i> Upcoming Tasks</h5>
                </div>
                <div class="card-body">
                    @if($upcoming_tasks->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($upcoming_tasks as $task)
                                <a href="{{ route('tasks.show', $task) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <i class="bi bi-file-text"></i> {{ $task->title }}
                                            </h6>
                                            <p class="mb-2 text-muted small">{{ Str::limit($task->description, 80) }}</p>
                                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-clock"></i> {{ $task->scheduled_at->format('M d, Y H:i') }}
                                                </span>
                                                <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'secondary') }}">
                                                    @if($task->priority === 'high') 🔴 @elseif($task->priority === 'medium') 🟡 @else 🟢 @endif
                                                    {{ ucfirst($task->priority) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-3">No upcoming scheduled tasks.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks by Priority and Quick Actions -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Tasks by Priority</h5>
                </div>
                <div class="card-body">
                    @if($tasksByPriority->count() > 0)
                        <ul class="list-group">
                            @foreach($tasksByPriority as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        @if($item->priority === 'high') 🔴 @elseif($item->priority === 'medium') 🟡 @else 🟢 @endif
                                        {{ ucfirst($item->priority) }} Priority
                                    </span>
                                    <span class="badge bg-{{ $item->priority === 'high' ? 'danger' : ($item->priority === 'medium' ? 'warning' : 'secondary') }} rounded-pill">
                                        {{ $item->count }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-graph-down text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No tasks available.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Task Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <h4 class="mb-0">
                                    @if($stats['total_tasks'] > 0)
                                        {{ round(($stats['completed_tasks'] / $stats['total_tasks']) * 100) }}%
                                    @else
                                        0%
                                    @endif
                                </h4>
                                <small class="text-muted">Completion Rate</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <h4 class="mb-0">{{ $stats['total_tasks'] - $stats['completed_tasks'] }}</h4>
                                <small class="text-muted">Active Tasks</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('tasks.index') }}" class="btn btn-primary">
                            <i class="bi bi-list-task"></i> View All Tasks
                        </a>
                        <a href="{{ route('calendar') }}" class="btn btn-primary">
                            <i class="bi bi-calendar3"></i> View Calendar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
