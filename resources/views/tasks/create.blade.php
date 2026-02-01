@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-plus-circle"></i> Create Task</h6>
                        <a href="{{ route('tasks.index') }}" class="btn btn-secondary btn-sm py-1">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body py-3">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                            <ul class="mb-0 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('tasks.store') }}" id="createTaskForm">
                        @csrf

                        <div class="row g-2 mb-2">
                            <div class="col-8">
                                <label for="title" class="form-label small mb-1">Title <span class="text-danger">*</span></label>
                                <input id="title" type="text" class="form-control form-control-sm @error('title') is-invalid @enderror"
                                    name="title" value="{{ old('title') }}" required autofocus placeholder="Task title">
                                @error('title')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-4">
                                <label for="priority" class="form-label small mb-1">Priority <span class="text-danger">*</span></label>
                                <select id="priority" class="form-select form-select-sm @error('priority') is-invalid @enderror" name="priority" required>
                                    <option value="">Select</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                                @error('priority')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="description" class="form-label small mb-1">Description</label>
                            <textarea id="description" class="form-control form-control-sm @error('description') is-invalid @enderror"
                                name="description" rows="2" placeholder="Optional description">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label for="assigned_to" class="form-label small mb-1">Assign To <span class="text-danger">*</span></label>
                                <select id="assigned_to" class="form-select form-select-sm @error('assigned_to') is-invalid @enderror" name="assigned_to" required>
                                    <option value="">Select User</option>
                                    @foreach($workers as $worker)
                                        <option value="{{ $worker->id }}" {{ old('assigned_to') == $worker->id ? 'selected' : '' }}>
                                            {{ $worker->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label for="scheduled_at" class="form-label small mb-1">Schedule <span class="text-danger">*</span></label>
                                <input id="scheduled_at" type="datetime-local" class="form-control form-control-sm @error('scheduled_at') is-invalid @enderror"
                                    name="scheduled_at" value="{{ old('scheduled_at') }}" required>
                                @error('scheduled_at')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small mb-1">Visibility <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="visibility" id="visibility_public" value="public" {{ old('visibility', 'public') == 'public' ? 'checked' : '' }} required>
                                    <label class="form-check-label small" for="visibility_public">
                                        <i class="bi bi-globe"></i> Public
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="visibility" id="visibility_private" value="private" {{ old('visibility') == 'private' ? 'checked' : '' }} required>
                                    <label class="form-check-label small" for="visibility_private">
                                        <i class="bi bi-lock-fill"></i> Private
                                    </label>
                                </div>
                            </div>
                            @error('visibility')
                                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('tasks.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check-circle"></i> Create
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
