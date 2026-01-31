@extends('layouts.app')

@section('content')
<div class="container">
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-0">
                    <i class="bi bi-clipboard-data"></i> Tasks per Worker Report
                </h2>
                <p class="text-muted mb-0 mt-2">View task distribution and performance across all workers</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-md-2 col-6 mb-3">
            <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
                <div class="stat-card">
                    <i class="bi bi-people text-primary"></i>
                    <h3>{{ $totals['total_workers'] }}</h3>
                    <p>Workers</p>
                </div>
            </a>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <a href="{{ route('tasks.index') }}" class="text-decoration-none">
                <div class="stat-card">
                    <i class="bi bi-list-check text-info"></i>
                    <h3>{{ $totals['total_tasks'] }}</h3>
                    <p>Tasks</p>
                </div>
            </a>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <a href="{{ route('tasks.index', ['status' => 'completed']) }}" class="text-decoration-none">
                <div class="stat-card">
                    <i class="bi bi-check-circle text-success"></i>
                    <h3>{{ $totals['total_completed'] }}</h3>
                    <p>Done</p>
                </div>
            </a>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <a href="{{ route('tasks.index', ['status' => 'in_progress']) }}" class="text-decoration-none">
                <div class="stat-card">
                    <i class="bi bi-arrow-repeat text-warning"></i>
                    <h3>{{ $totals['total_in_progress'] }}</h3>
                    <p>Active</p>
                </div>
            </a>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <a href="{{ route('tasks.index', ['status' => 'pending']) }}" class="text-decoration-none">
                <div class="stat-card">
                    <i class="bi bi-clock-history text-secondary"></i>
                    <h3>{{ $totals['total_pending'] }}</h3>
                    <p>Pending</p>
                </div>
            </a>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <a href="{{ route('tasks.index', ['status' => 'overdue']) }}" class="text-decoration-none">
                <div class="stat-card">
                    <i class="bi bi-exclamation-triangle text-danger"></i>
                    <h3>{{ $totals['total_overdue'] }}</h3>
                    <p>Overdue</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Worker Performance Table -->
    <div class="row">
        <div class="col-12">
            <div class="card hover-lift">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0"><i class="bi bi-table"></i> Worker Performance Breakdown</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Worker</th>
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
                                @foreach($workers as $worker)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person-circle text-primary me-2" style="font-size: 1.5rem;"></i>
                                                <div>
                                                    <strong>{{ $worker->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $worker->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info rounded-pill">{{ $worker->total_tasks }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success rounded-pill">{{ $worker->completed_tasks }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning rounded-pill">{{ $worker->in_progress_tasks }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary rounded-pill">{{ $worker->pending_tasks }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger rounded-pill">{{ $worker->overdue_tasks }}</span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $completionRate = $worker->total_tasks > 0
                                                    ? round(($worker->completed_tasks / $worker->total_tasks) * 100, 1)
                                                    : 0;
                                            @endphp
                                            <div class="progress" style="height: 20px; min-width: 100px;">
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

    <!-- Charts Section -->
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card hover-lift">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-fill"></i> Top Performers</h5>
                </div>
                <div class="card-body">
                    @php
                        $topPerformers = $workers->sortByDesc(function($worker) {
                            return $worker->total_tasks > 0 ? ($worker->completed_tasks / $worker->total_tasks) : 0;
                        })->take(5);
                    @endphp
                    <ul class="list-group">
                        @foreach($topPerformers as $index => $worker)
                            @php
                                $rate = $worker->total_tasks > 0
                                    ? round(($worker->completed_tasks / $worker->total_tasks) * 100, 1)
                                    : 0;
                            @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary rounded-pill me-2">#{{ $index + 1 }}</span>
                                    <strong>{{ $worker->name }}</strong>
                                </div>
                                <span class="badge bg-success">{{ $rate }}%</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card hover-lift">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <h5 class="mb-0"><i class="bi bi-pie-chart-fill"></i> Workload Distribution</h5>
                </div>
                <div class="card-body">
                    @php
                        $topWorkload = $workers->sortByDesc('total_tasks')->take(5);
                    @endphp
                    <ul class="list-group">
                        @foreach($topWorkload as $index => $worker)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-info rounded-pill me-2">#{{ $index + 1 }}</span>
                                    <strong>{{ $worker->name }}</strong>
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $worker->total_tasks }} tasks</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
