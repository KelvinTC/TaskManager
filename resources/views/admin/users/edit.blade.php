@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Edit User: {{ $user->name }}</h4>
                </div>

                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name', $user->name) }}" required autofocus>

                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email', $user->email) }}" required>

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number (WhatsApp)</label>
                            <input id="phone" type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+1234567890">

                            @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Include country code (e.g., +1234567890)</small>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" class="form-select @error('role') is-invalid @enderror" name="role" required>
                                @if(Auth::user()->isSuperAdmin())
                                    <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>
                                        Super Admin (Full System Access)
                                    </option>
                                @endif
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                                    Admin (Can create & assign tasks)
                                </option>
                                <option value="employee" {{ old('role', $user->role) == 'employee' ? 'selected' : '' }}>
                                    Employee (Receives & completes tasks)
                                </option>
                            </select>

                            @error('role')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="preferred_channel" class="form-label">Preferred Notification Channel</label>
                            <select id="preferred_channel" class="form-select @error('preferred_channel') is-invalid @enderror"
                                    name="preferred_channel" required>
                                <option value="whatsapp" {{ old('preferred_channel', $user->preferred_channel) == 'whatsapp' ? 'selected' : '' }}>
                                    WhatsApp
                                </option>
                                <option value="in_app" {{ old('preferred_channel', $user->preferred_channel) == 'in_app' ? 'selected' : '' }}>
                                    In-App Only
                                </option>
                            </select>

                            @error('preferred_channel')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">WhatsApp requires a valid phone number</small>
                        </div>

                        @if($user->isSuperAdmin())
                            <div class="alert alert-warning">
                                <strong>Note:</strong> This is a Super Admin account. Some restrictions apply to prevent accidental demotion.
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
