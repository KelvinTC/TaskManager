@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>User Management</h2>
                <a href="{{ route('admin.users.invite.form') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i> Invite New User
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Pending Invitations -->
            @if($invitedUsers->count() > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Pending Invitations ({{ $invitedUsers->total() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Invited By</th>
                                    <th>Invited On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invitedUsers as $invited)
                                <tr>
                                    <td>{{ $invited->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $invited->role === 'admin' ? 'primary' : 'secondary' }}">
                                            {{ ucfirst($invited->role) }}
                                        </span>
                                    </td>
                                    <td>{{ $invited->inviter->name }}</td>
                                    <td>{{ $invited->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <form action="{{ route('admin.invitations.destroy', $invited) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove this invitation?')">
                                                Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($invitedUsers->hasPages())
                        <div class="mt-3">
                            {{ $invitedUsers->links() }}
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Registered Users -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Registered Users ({{ $users->total() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Registered On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->phone ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $user->role === 'admin' ? 'primary' : 'secondary' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            @if($user->role === 'employee')
                                                <form action="{{ route('admin.users.promote', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Make this user an Admin?')">
                                                        <i class="bi bi-arrow-up-circle"></i> Make Admin
                                                    </button>
                                                </form>
                                            @elseif($user->role === 'admin')
                                                <form action="{{ route('admin.users.demote', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Change this user to Employee?')">
                                                        <i class="bi bi-arrow-down-circle"></i> Make Employee
                                                    </button>
                                                </form>
                                            @endif
                                            @if(Auth::user()->isSuperAdmin() && $user->id !== Auth::id())
                                                <form action="{{ route('admin.users.delete', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone!')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No users registered yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($users->hasPages())
                        <div class="mt-3">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
