@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Create New Task</h4>
                        <a href="{{ route('tasks.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Tasks
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Validation Errors</h5>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('tasks.store') }}" id="createTaskForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label fw-bold">Task Title <span class="text-danger">*</span></label>
                                <input id="title" type="text" class="form-control form-control-lg @error('title') is-invalid @enderror"
                                    name="title" value="{{ old('title') }}" required autofocus placeholder="Enter task title...">

                                @error('title')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="priority" class="form-label fw-bold">Priority <span class="text-danger">*</span></label>
                                <select id="priority" class="form-select form-select-lg @error('priority') is-invalid @enderror" name="priority" required>
                                    <option value="">-- Select Priority --</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>🟢 Low</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>🟡 Medium</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>🔴 High</option>
                                </select>

                                @error('priority')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Description</label>
                            <textarea id="description" class="form-control @error('description') is-invalid @enderror"
                                name="description" rows="5" placeholder="Enter detailed task description...">{{ old('description') }}</textarea>

                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="assigned_to" class="form-label fw-bold">Assign To <span class="text-danger">*</span></label>
                                <select id="assigned_to" class="form-select form-select-lg @error('assigned_to') is-invalid @enderror" name="assigned_to" required>
                                    <option value="">-- Select Employee --</option>
                                    @foreach($workers as $worker)
                                        <option value="{{ $worker->id }}" {{ old('assigned_to') == $worker->id ? 'selected' : '' }}>
                                            {{ $worker->name }} ({{ $worker->email }})
                                        </option>
                                    @endforeach
                                </select>

                                @error('assigned_to')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="scheduled_at" class="form-label fw-bold">Schedule Date & Time <span class="text-danger">*</span></label>
                                <input id="scheduled_at" type="datetime-local" class="form-control form-control-lg @error('scheduled_at') is-invalid @enderror"
                                    name="scheduled_at" value="{{ old('scheduled_at') }}" required>

                                @error('scheduled_at')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Note:</strong> The assigned employee will be notified about this task via their preferred channel.
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('tasks.index') }}" class="btn btn-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Create Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createTaskForm');

    form.addEventListener('submit', function(e) {
        console.log('Form submitted!');
        console.log('Action:', form.action);
        console.log('Method:', form.method);

        // Check all required fields
        const title = document.querySelector('[name="title"]').value;
        const priority = document.querySelector('[name="priority"]').value;
        const assignedTo = document.querySelector('[name="assigned_to"]').value;
        const scheduledAt = document.querySelector('[name="scheduled_at"]').value;

        console.log('Title:', title);
        console.log('Priority:', priority);
        console.log('Assigned To:', assignedTo);
        console.log('Scheduled At:', scheduledAt);

        if (!title || !priority || !assignedTo || !scheduledAt) {
            console.error('Missing required fields!');
            alert('Please fill in all required fields');
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endpush

@endsection
