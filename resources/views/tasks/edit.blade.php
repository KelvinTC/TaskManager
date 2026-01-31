@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Task</h5>
                        <a href="{{ route('tasks.show', $task) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('tasks.update', $task) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                                <input id="title" type="text" class="form-control @error('title') is-invalid @enderror"
                                    name="title" value="{{ old('title', $task->title) }}" required autofocus>

                                @error('title')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select id="priority" class="form-select @error('priority') is-invalid @enderror" name="priority" required>
                                    <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>High</option>
                                </select>

                                @error('priority')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" class="form-control @error('description') is-invalid @enderror"
                                name="description" rows="3">{{ old('description', $task->description) }}</textarea>

                            @error('description')
                                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="assigned_to" class="form-label">Assign To <span class="text-danger">*</span></label>
                                <select id="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror" name="assigned_to" required>
                                    @foreach($workers as $worker)
                                        <option value="{{ $worker->id }}" {{ old('assigned_to', $task->assigned_to) == $worker->id ? 'selected' : '' }}>
                                            {{ $worker->name }} ({{ ucfirst($worker->role) }})
                                        </option>
                                    @endforeach
                                </select>

                                @error('assigned_to')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="scheduled_at" class="form-label">Schedule Date & Time <span class="text-danger">*</span></label>
                                <input id="scheduled_at" type="datetime-local" class="form-control @error('scheduled_at') is-invalid @enderror"
                                    name="scheduled_at" value="{{ old('scheduled_at', $task->scheduled_at ? $task->scheduled_at->format('Y-m-d\TH:i') : '') }}" required>

                                @error('scheduled_at')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select id="status" class="form-select @error('status') is-invalid @enderror" name="status" required>
                                    <option value="pending" {{ old('status', $task->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>

                                @error('status')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Visibility <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check p-2 border rounded">
                                        <input class="form-check-input" type="radio" name="visibility" id="visibility_public" value="public" {{ old('visibility', $task->visibility ?? 'public') == 'public' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="visibility_public">
                                            <i class="bi bi-globe"></i> Public
                                            <small class="d-block text-muted">Visible to everyone</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check p-2 border rounded">
                                        <input class="form-check-input" type="radio" name="visibility" id="visibility_private" value="private" {{ old('visibility', $task->visibility ?? 'public') == 'private' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="visibility_private">
                                            <i class="bi bi-lock-fill"></i> Private
                                            <small class="d-block text-muted">Only assignee and creator</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('visibility')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('tasks.show', $task) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
