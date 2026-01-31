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

            <!-- Search and Filter Bar -->
            <div class="card shadow mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('tasks.index') }}" id="filterForm">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label for="search" class="form-label small">Search</label>
                                <input type="text"
                                       class="form-control"
                                       id="search"
                                       name="search"
                                       placeholder="Search tasks..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label small">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="priority" class="form-label small">Priority</label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="">All Priority</option>
                                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="per_page" class="form-label small">Show</label>
                                <select class="form-select" id="per_page" name="per_page">
                                    <option value="5" {{ request('per_page', 10) == 5 ? 'selected' : '' }}>5</option>
                                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end gap-1">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                                @if(request()->hasAny(['search', 'status', 'priority']))
                                    <a href="{{ route('tasks.index') }}" class="btn btn-secondary" title="Clear filters">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

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
                                    <th>Visibility</th>
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
                                                {{ $task->assignedTo->name }}
                                                <br><small class="text-muted">{{ ucfirst($task->assignedTo->role) }}</small>
                                            @else
                                                <span class="text-muted">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($task->status === 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($task->status === 'in_progress')
                                                <span class="badge bg-primary">In Progress</span>
                                            @else
                                                <span class="badge bg-secondary">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($task->priority === 'high')
                                                <span class="badge bg-danger">High</span>
                                            @elseif($task->priority === 'medium')
                                                <span class="badge bg-warning text-dark">Medium</span>
                                            @else
                                                <span class="badge bg-light text-dark">Low</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(($task->visibility ?? 'public') === 'private')
                                                <i class="bi bi-lock-fill text-muted" title="Private"></i>
                                            @else
                                                <i class="bi bi-globe text-muted" title="Public"></i>
                                            @endif
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
                                        <td colspan="7" class="text-center py-4">
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

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Showing {{ $tasks->firstItem() ?? 0 }} to {{ $tasks->lastItem() ?? 0 }} of {{ $tasks->total() }} tasks
                        </div>
                        <div>
                            {{ $tasks->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-submit form when filters change
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');
        const statusSelect = document.getElementById('status');
        const prioritySelect = document.getElementById('priority');
        const perPageSelect = document.getElementById('per_page');

        // Auto submit when dropdowns change
        [statusSelect, prioritySelect, perPageSelect].forEach(select => {
            if (select) {
                select.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
        });

        // Submit on Enter key in search field
        const searchInput = document.getElementById('search');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filterForm.submit();
                }
            });
        }
    });
</script>
@endpush
@endsection
