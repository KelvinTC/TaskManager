@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-1"><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>
            <p class="text-muted">Monitor and manage your team's performance</p>
        </div>
        <div class="col-md-4 text-end">
            <button onclick="downloadCSV()" class="btn btn-info me-2">
                <i class="bi bi-download"></i> Export CSV
            </button>
            <a href="{{ route('tasks.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> New Task
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-list-check text-primary mb-2" style="font-size: 2.5rem;"></i>
                    <h3 class="mb-0">{{ $stats['total_tasks'] }}</h3>
                    <p class="text-muted mb-0">Total Tasks</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-clock-history text-warning mb-2" style="font-size: 2.5rem;"></i>
                    <h3 class="mb-0">{{ $stats['pending_tasks'] + $stats['in_progress_tasks'] }}</h3>
                    <p class="text-muted mb-0">Active</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success mb-2" style="font-size: 2.5rem;"></i>
                    <h3 class="mb-0">{{ $stats['completed_tasks'] }}</h3>
                    <p class="text-muted mb-0">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle text-danger mb-2" style="font-size: 2.5rem;"></i>
                    <h3 class="mb-0">{{ $stats['overdue_tasks'] }}</h3>
                    <p class="text-muted mb-0">Overdue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button">
                <i class="bi bi-graph-up"></i> Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="team-tab" data-bs-toggle="tab" data-bs-target="#team" type="button">
                <i class="bi bi-people"></i> Team Performance
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button">
                <i class="bi bi-list-task"></i> Recent Tasks
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button">
                <i class="bi bi-file-earmark-bar-graph"></i> Reports & Filters
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="dashboardTabContent">

        <!-- Overview Tab -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            @if($stats['total_tasks'] > 0)
                <div class="row">
                    <!-- Status Chart -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title mb-4"><i class="bi bi-pie-chart"></i> Task Status</h5>
                                <canvas id="statusChart" height="280"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Priority Chart -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title mb-4"><i class="bi bi-bar-chart"></i> Priority Levels</h5>
                                <canvas id="priorityChart" height="280"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Completion Rate -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title mb-4"><i class="bi bi-speedometer2"></i> Completion Rate</h5>
                                <canvas id="completionGauge" height="200"></canvas>
                                <h2 class="mt-3">{{ round(($stats['completed_tasks'] / $stats['total_tasks']) * 100) }}%</h2>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox text-muted mb-3" style="font-size: 4rem;"></i>
                        <h4 class="text-muted">No Tasks Yet</h4>
                        <p class="text-muted mb-4">Create your first task to see analytics and charts</p>
                        <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle"></i> Create First Task
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Team Performance Tab -->
        <div class="tab-pane fade" id="team" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="bi bi-people"></i> Employee Performance Overview</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Completed</th>
                                    <th class="text-center">Active</th>
                                    <th class="text-center">Overdue</th>
                                    <th>Completion Rate</th>
                                    <th class="text-center">Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $employee)
                                    @php
                                        $empStats = $employee->assignedTasks;
                                        $total = $empStats->count();
                                        $completed = $empStats->where('status', 'completed')->count();
                                        $active = $empStats->whereIn('status', ['pending', 'in_progress'])->count();
                                        $overdue = $empStats->filter(function($task) {
                                            return $task->scheduled_at && $task->scheduled_at->isPast() && $task->status !== 'completed';
                                        })->count();
                                        $completionRate = $total > 0 ? round(($completed / $total) * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $employee->name }}</strong></td>
                                        <td class="text-center">{{ $total }}</td>
                                        <td class="text-center"><span class="badge bg-success">{{ $completed }}</span></td>
                                        <td class="text-center"><span class="badge bg-warning">{{ $active }}</span></td>
                                        <td class="text-center"><span class="badge bg-danger">{{ $overdue }}</span></td>
                                        <td>
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar bg-{{ $completionRate >= 75 ? 'success' : ($completionRate >= 50 ? 'warning' : 'danger') }}"
                                                     style="width: {{ $completionRate }}%">
                                                    {{ $completionRate }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($completionRate >= 75)
                                                <span class="badge bg-success">⭐ Excellent</span>
                                            @elseif($completionRate >= 50)
                                                <span class="badge bg-warning">👍 Good</span>
                                            @else
                                                <span class="badge bg-secondary">📈 Growing</span>
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

        <!-- Recent Tasks Tab -->
        <div class="tab-pane fade" id="tasks" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="bi bi-clock-history"></i> Recent Tasks</h5>
                    @if($recent_tasks->count() > 0)
                        <div class="list-group">
                            @foreach($recent_tasks as $task)
                                <a href="{{ route('tasks.show', $task) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $task->title }}</h6>
                                            <p class="mb-2 text-muted small">{{ Str::limit($task->description, 100) }}</p>
                                            <div class="d-flex gap-2">
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-person"></i> {{ $task->assignedTo ? $task->assignedTo->name : 'Unassigned' }}
                                                </span>
                                                <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'info' : 'warning') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                </span>
                                                <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($task->priority) }} Priority
                                                </span>
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $task->created_at->diffForHumans() }}</small>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted mb-3" style="font-size: 4rem;"></i>
                            <h5 class="text-muted">No Recent Tasks</h5>
                            <p class="text-muted">Tasks will appear here once you create them</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Reports Tab -->
        <div class="tab-pane fade" id="reports" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="bi bi-funnel"></i> Filter Reports</h5>

                    <form method="GET" action="{{ route('dashboard') }}">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Priority</label>
                                <select class="form-select" name="priority">
                                    <option value="">All Priorities</option>
                                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Employee</label>
                                <select class="form-select" name="assigned_to">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ request('assigned_to') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Apply Filters
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Export Options</h6>
                            <button onclick="downloadCSV()" class="btn btn-outline-success w-100 mb-2">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Download CSV Report
                            </button>
{{--                            <button onclick="downloadJSON()" class="btn btn-outline-primary w-100 mb-2">--}}
{{--                                <i class="bi bi-file-earmark-text"></i> Download JSON Report--}}
{{--                            </button>--}}
                            <button onclick="window.print()" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-printer"></i> Print Dashboard
                            </button>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Quick Stats</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Total Tasks Created</span>
                                    <strong>{{ $stats['total_tasks'] }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Completion Rate</span>
                                    <strong>
                                        @if($stats['total_tasks'] > 0)
                                            {{ round(($stats['completed_tasks'] / $stats['total_tasks']) * 100) }}%
                                        @else
                                            0%
                                        @endif
                                    </strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Active Employees</span>
                                    <strong>{{ $employees->count() }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Chart Data
const statusData = {
    labels: ['Pending', 'In Progress', 'Completed'],
    datasets: [{
        data: [{{ $stats['pending_tasks'] }}, {{ $stats['in_progress_tasks'] }}, {{ $stats['completed_tasks'] }}],
        backgroundColor: ['#fbbf24', '#3b82f6', '#10b981'],
        borderWidth: 2,
        borderColor: '#fff'
    }]
};

const priorityData = {
    labels: ['Low', 'Medium', 'High'],
    datasets: [{
        label: 'Tasks',
        data: [
            {{ $tasksByPriority->where('priority', 'low')->first()->count ?? 0 }},
            {{ $tasksByPriority->where('priority', 'medium')->first()->count ?? 0 }},
            {{ $tasksByPriority->where('priority', 'high')->first()->count ?? 0 }}
        ],
        backgroundColor: ['#9ca3af', '#fbbf24', '#ef4444'],
        borderRadius: 5
    }]
};

const completionRate = {{ $stats['total_tasks'] > 0 ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100) : 0 }};
const hasTasks = {{ $stats['total_tasks'] > 0 ? 'true' : 'false' }};

// Only initialize charts if there are tasks
if (hasTasks) {
    // Status Doughnut Chart
    const statusChartEl = document.getElementById('statusChart');
    if (statusChartEl) {
        new Chart(statusChartEl, {
            type: 'doughnut',
            data: statusData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 15, font: { size: 12 } } }
                }
            }
        });
    }

    // Priority Bar Chart
    const priorityChartEl = document.getElementById('priorityChart');
    if (priorityChartEl) {
        new Chart(priorityChartEl, {
            type: 'bar',
            data: priorityData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // Completion Gauge
    const gaugeChartEl = document.getElementById('completionGauge');
    if (gaugeChartEl) {
        new Chart(gaugeChartEl, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [completionRate, 100 - completionRate],
                    backgroundColor: [
                        completionRate >= 75 ? '#10b981' : (completionRate >= 50 ? '#fbbf24' : '#ef4444'),
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
                cutout: '75%',
                plugins: { legend: { display: false }, tooltip: { enabled: false } }
            }
        });
    }
}

// Download CSV Report
function downloadCSV() {
    const employeeData = [
        @foreach($employees as $employee)
            @php
                $empStats = $employee->assignedTasks;
                $total = $empStats->count();
                $completed = $empStats->where('status', 'completed')->count();
                $active = $empStats->whereIn('status', ['pending', 'in_progress'])->count();
                $overdue = $empStats->filter(function($task) {
                    return $task->scheduled_at && $task->scheduled_at->isPast() && $task->status !== 'completed';
                })->count();
                $rate = $total > 0 ? round(($completed / $total) * 100) : 0;
            @endphp
            {
                name: "{{ $employee->name }}",
                email: "{{ $employee->email }}",
                total: {{ $total }},
                completed: {{ $completed }},
                active: {{ $active }},
                overdue: {{ $overdue }},
                completionRate: {{ $rate }}
            },
        @endforeach
    ];

    // CSV Header
    let csv = 'Employee Name,Email,Total Tasks,Completed,Active,Overdue,Completion Rate\n';

    // CSV Data
    employeeData.forEach(emp => {
        csv += `"${emp.name}","${emp.email}",${emp.total},${emp.completed},${emp.active},${emp.overdue},${emp.completionRate}%\n`;
    });

    // Add summary at the bottom
    csv += '\n';
    csv += 'Summary Statistics\n';
    csv += `Total Tasks,{{ $stats['total_tasks'] }}\n`;
    csv += `Pending Tasks,{{ $stats['pending_tasks'] }}\n`;
    csv += `In Progress Tasks,{{ $stats['in_progress_tasks'] }}\n`;
    csv += `Completed Tasks,{{ $stats['completed_tasks'] }}\n`;
    csv += `Overdue Tasks,{{ $stats['overdue_tasks'] }}\n`;
    csv += `Overall Completion Rate,${completionRate}%\n`;
    csv += `Report Generated,${new Date().toLocaleString()}\n`;

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `task_report_${Date.now()}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}

// Download JSON Report
function downloadJSON() {
    const report = {
        generated: new Date().toLocaleString(),
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
                @endphp
                {
                    name: "{{ $employee->name }}",
                    email: "{{ $employee->email }}",
                    tasks: {{ $total }},
                    completed: {{ $completed }},
                    rate: "{{ $total > 0 ? round(($completed / $total) * 100) : 0 }}%"
                },
            @endforeach
        ]
    };

    const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `task_report_${Date.now()}.json`;
    a.click();
    URL.revokeObjectURL(url);
}
</script>
@endsection
