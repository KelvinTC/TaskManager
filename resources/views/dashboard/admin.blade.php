@extends('layouts.app')

@section('content')
<div class="container animate-fadeInUp">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-12 col-md-8 mb-3 mb-md-0">
                <h2 class="mb-0">
                    <i class="bi bi-shield-check"></i>
                    <span class="d-none d-sm-inline">Welcome, {{ Auth::user()->name }}</span>
                    <span class="d-inline d-sm-none">Welcome</span>
                </h2>
                <p class="text-muted mb-0 mt-2 d-none d-sm-block">Welcome back! Here's what's happening with your tasks today.</p>
            </div>
            <div class="col-12 col-md-4 text-md-end">
                <div class="d-grid d-md-block gap-2">
                    <a href="{{ route('tasks.create') }}" class="btn btn-success btn-lg me-md-2 mb-2 mb-md-0">
                        <i class="bi bi-plus-circle"></i> Create Task
                    </a>
                    @if(Auth::user()->isSuperAdmin())
                        <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-people"></i> <span class="d-none d-sm-inline">Manage</span> Users
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-6 col-md-4 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="bi bi-list-check text-primary" style="font-size: 2rem;"></i>
                    <h3 class="text-primary mb-0 mt-2">{{ $stats['total_tasks'] }}</h3>
                    <p class="mb-0 small text-muted">Total Tasks</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <h3 class="text-success mb-0 mt-2">{{ $stats['completed_tasks'] }}</h3>
                    <p class="mb-0 small text-muted">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 mb-3">
            <div class="card text-center shadow-sm">
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
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock"></i> Recent Tasks</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($recent_tasks as $task)
                            <a href="{{ route('tasks.show', $task) }}" class="list-group-item list-group-item-action priority-{{ $task->priority }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">
                                            <i class="bi bi-file-text text-primary"></i> {{ $task->title }}
                                        </h6>
                                        <p class="mb-2 text-muted small">{{ Str::limit($task->description, 100) }}</p>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="badge bg-light text-dark">
                                                <i class="bi bi-person"></i> {{ $task->assignedTo ? $task->assignedTo->name : 'Unassigned' }}
                                            </span>
                                            <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'info' : 'warning') }}">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                            <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'secondary') }}">
                                                @if($task->priority === 'high') 🔴 @elseif($task->priority === 'medium') 🟡 @else 🟢 @endif
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $task->created_at->diffForHumans() }}</small>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('dashboard') }}">
                        <div class="mb-3">
                            <label for="statusFilter" class="form-label">Status</label>
                            <select class="form-select" id="statusFilter" name="status">
                                <option value="">All</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="priorityFilter" class="form-label">Priority</label>
                            <select class="form-select" id="priorityFilter" name="priority">
                                <option value="">All</option>
                                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="employeeFilter" class="form-label">Assigned To</label>
                            <select class="form-select" id="employeeFilter" name="assigned_to">
                                <option value="">All Employees</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ request('assigned_to') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Apply
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Tasks by Status</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($tasksByStatus as $item)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="bi bi-circle-fill text-{{ $item->status === 'completed' ? 'success' : ($item->status === 'in_progress' ? 'info' : 'warning') }}"></i>
                                    {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                </span>
                                <span class="badge bg-{{ $item->status === 'completed' ? 'success' : ($item->status === 'in_progress' ? 'info' : 'warning') }} rounded-pill">
                                    {{ $item->count }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-3">
                        <div class="progress" style="height: 25px;">
                            @php
                                $total = $tasksByStatus->sum('count');
                            @endphp
                            @foreach($tasksByStatus as $item)
                                @php
                                    $percentage = $total > 0 ? ($item->count / $total) * 100 : 0;
                                @endphp
                                <div class="progress-bar bg-{{ $item->status === 'completed' ? 'success' : ($item->status === 'in_progress' ? 'info' : 'warning') }}"
                                     role="progressbar"
                                     style="width: {{ $percentage }}%"
                                     title="{{ ucfirst($item->status) }}: {{ $item->count }}">
                                    {{ round($percentage) }}%
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
