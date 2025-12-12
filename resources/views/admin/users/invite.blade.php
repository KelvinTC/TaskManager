@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Invite New User</h4>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.users.invite') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus>

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">The user will be able to register using this email address.</small>
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number (WhatsApp) <span class="text-muted">(Optional)</span></label>
                            <input id="phone_number" type="tel" class="form-control @error('phone_number') is-invalid @enderror" name="phone_number" value="{{ old('phone_number') }}" placeholder="+1234567890">

                            @error('phone_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Include country code (e.g., +1234567890). If provided, user will receive WhatsApp invitation.</small>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Assign Role</label>
                            <select id="role" class="form-select @error('role') is-invalid @enderror" name="role" required>
                                <option value="">-- Select Role --</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin (Can create & assign tasks)</option>
                                <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee (Receives & completes tasks)</option>
                            </select>

                            @error('role')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <strong>Note:</strong> The invited user will receive an invitation to register. They must use the invited email address during registration.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Send Invitation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
