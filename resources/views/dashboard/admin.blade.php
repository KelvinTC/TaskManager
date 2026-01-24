@extends('layouts.app')

@section('content')
<div class="container-fluid animate-fadeInUp">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-12 col-md-6 mb-3 mb-md-0">
                <h2 class="mb-0">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span class="d-none d-sm-inline">Admin Dashboard</span>
                    <span class="d-inline d-sm-none">Dashboard</span>
                </h2>
                <p class="text-muted mb-0 mt-2 d-none d-sm-block">Comprehensive analytics and team performance overview</p>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <div class="d-grid d-md-block gap-2">
                    <button onclick="downloadReport()" class="btn btn-info btn-lg me-md-2 mb-2 mb-md-0">
                        <i class="bi bi-download"></i> Download Report
                    </button>
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

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filters & Date Range</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label for="statusFilter" class="form-label">Status</label>
                        <select class="form-select" id="statusFilter" name="status">
                            <option value="">All</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="priorityFilter" class="form-label">Priority</label>
                        <select class="form-select" id="priorityFilter" name="priority">
                            <option value="">All</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="employeeFilter" class="form-label">Employee</label>
                        <select class="form-select" id="employeeFilter" name="assigned_to">
                            <option value="">All Employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('assigned_to') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="dateFrom" class="form-label">From Date</label>
                        <input type="date" class="form-select" id="dateFrom" name="date_from" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="dateTo" class="form-label">To Date</label>
                        <input type="date" class="form-select" id="dateTo" name="date_to" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="card text-center shadow-sm h-100">
                <div class="card-body">
                    <i class="bi bi-list-check text-primary" style="font-size: 2rem;"></i>
                    <h3 class="text-primary mb-0 mt-2">{{ $stats['total_tasks'] }}</h3>
                    <p class="mb-0 small text-muted">Total Tasks</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card text-center shadow-sm h-100">
                <div class="card-body">
                    <i class="bi bi-hourglass-split text-warning" style="font-size: 2rem;"></i>
                    <h3 class="text-warning mb-0 mt-2">{{ $stats['pending_tasks'] + $stats['in_progress_tasks'] }}</h3>
                    <p class="mb-0 small text-muted">Active</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card text-center shadow-sm h-100">
                <div class="card-body">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <h3 class="text-success mb-0 mt-2">{{ $stats['completed_tasks'] }}</h3>
                    <p class="mb-0 small text-muted">Completed</p>
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

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Task Status Pie Chart -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Tasks by Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusPieChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Priority Distribution -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Priority Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="priorityChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Completion Rate -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-speedometer2"></i> Completion Rate</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <canvas id="completionGauge" height="200"></canvas>
                    <h2 class="mt-3 mb-0">
                        @if($stats['total_tasks'] > 0)
                            {{ round(($stats['completed_tasks'] / $stats['total_tasks']) * 100) }}%
                        @else
                            0%
                        @endif
                    </h2>
                    <p class="text-muted">Overall Completion</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Performance Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Employee Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th class="text-center">Total Tasks</th>
                                    <th class="text-center">Completed</th>
                                    <th class="text-center">In Progress</th>
                                    <th class="text-center">Pending</th>
                                    <th class="text-center">Overdue</th>
                                    <th class="text-center">Completion Rate</th>
                                    <th class="text-center">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $employee)
                                    @php
                                        $empStats = $employee->assignedTasks;
                                        $total = $empStats->count();
                                        $completed = $empStats->where('status', 'completed')->count();
                                        $inProgress = $empStats->where('status', 'in_progress')->count();
                                        $pending = $empStats->where('status', 'pending')->count();
                                        $overdue = $empStats->filter(function($task) {
                                            return $task->scheduled_at && $task->scheduled_at->isPast() && $task->status !== 'completed';
                                        })->count();
                                        $completionRate = $total > 0 ? round(($completed / $total) * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $employee->name }}</strong></td>
                                        <td class="text-center">{{ $total }}</td>
                                        <td class="text-center"><span class="badge bg-success">{{ $completed }}</span></td>
                                        <td class="text-center"><span class="badge bg-info">{{ $inProgress }}</span></td>
                                        <td class="text-center"><span class="badge bg-warning">{{ $pending }}</span></td>
                                        <td class="text-center"><span class="badge bg-danger">{{ $overdue }}</span></td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $completionRate >= 75 ? 'success' : ($completionRate >= 50 ? 'warning' : 'danger') }}"
                                                     role="progressbar"
                                                     style="width: {{ $completionRate }}%">
                                                    {{ $completionRate }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($completionRate >= 75)
                                                <span class="badge bg-success">Excellent</span>
                                            @elseif($completionRate >= 50)
                                                <span class="badge bg-warning">Good</span>
                                            @else
                                                <span class="badge bg-danger">Needs Improvement</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tasks -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Tasks</h5>
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
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Prepare data
const statusData = {
    labels: ['Pending', 'In Progress', 'Completed'],
    datasets: [{
        data: [{{ $stats['pending_tasks'] }}, {{ $stats['in_progress_tasks'] }}, {{ $stats['completed_tasks'] }}],
        backgroundColor: ['#f59e0b', '#0ea5e9', '#10b981'],
        borderWidth: 0
    }]
};

const priorityData = {
    labels: ['Low', 'Medium', 'High'],
    datasets: [{
        data: [
            {{ $tasksByPriority->where('priority', 'low')->first()->count ?? 0 }},
            {{ $tasksByPriority->where('priority', 'medium')->first()->count ?? 0 }},
            {{ $tasksByPriority->where('priority', 'high')->first()->count ?? 0 }}
        ],
        backgroundColor: ['#6b7280', '#f59e0b', '#ef4444'],
        borderWidth: 0
    }]
};

const completionRate = {{ $stats['total_tasks'] > 0 ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100) : 0 }};

// Status Pie Chart
const statusCtx = document.getElementById('statusPieChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: statusData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Priority Bar Chart
const priorityCtx = document.getElementById('priorityChart').getContext('2d');
new Chart(priorityCtx, {
    type: 'bar',
    data: priorityData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Completion Gauge (Doughnut)
const completionCtx = document.getElementById('completionGauge').getContext('2d');
new Chart(completionCtx, {
    type: 'doughnut',
    data: {
        datasets: [{
            data: [completionRate, 100 - completionRate],
            backgroundColor: [
                completionRate >= 75 ? '#10b981' : (completionRate >= 50 ? '#f59e0b' : '#ef4444'),
                '#e5e7eb'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        circumference: 180,
        rotation: -90,
        cutout: '70%',
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                enabled: false
            }
        }
    }
});

// Download Report Function
function downloadReport() {
    const reportData = {
        generatedAt: new Date().toLocaleString(),
        stats: {
            total: {{ $stats['total_tasks'] }},
            pending: {{ $stats['pending_tasks'] }},
            inProgress: {{ $stats['in_progress_tasks'] }},
            completed: {{ $stats['completed_tasks'] }},
            overdue: {{ $stats['overdue_tasks'] }},
            completionRate: completionRate + '%'
        },
        employees: [
            @foreach($employees as $employee)
                @php
                    $empStats = $employee->assignedTasks;
                    $total = $empStats->count();
                    $completed = $empStats->where('status', 'completed')->count();
                    $rate = $total > 0 ? round(($completed / $total) * 100) : 0;
                @endphp
                {
                    name: "{{ $employee->name }}",
                    email: "{{ $employee->email }}",
                    totalTasks: {{ $total }},
                    completed: {{ $completed }},
                    completionRate: {{ $rate }}
                },
            @endforeach
        ]
    };

    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(reportData, null, 2));
    const downloadAnchorNode = document.createElement('a');
    downloadAnchorNode.setAttribute("href", dataStr);
    downloadAnchorNode.setAttribute("download", "task_report_" + Date.now() + ".json");
    document.body.appendChild(downloadAnchorNode);
    downloadAnchorNode.click();
    downloadAnchorNode.remove();
}
</script>
@endsection