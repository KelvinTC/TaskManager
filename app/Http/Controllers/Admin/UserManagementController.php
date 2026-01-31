<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppInvitation;
use App\Models\InvitedUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Allow both super_admin and admin to access user management
            if (!Auth::user()->canManageUsers()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        })->except(['edit', 'update']);

        // Only super_admin can invite new users
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->canInviteUsers()) {
                abort(403, 'Only Super Admin can invite users.');
            }
            return $next($request);
        })->only(['inviteForm', 'invite']);
    }

    public function index()
    {
        $users = User::where('role', '!=', 'super_admin')
            ->orderBy('created_at', 'desc')
            ->get();

        $invitedUsers = InvitedUser::with('inviter')
            ->notRegistered()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.users.index', compact('users', 'invitedUsers'));
    }

    public function inviteForm()
    {
        return view('admin.users.invite');
    }

    public function invite(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|regex:/^\+[1-9]\d{1,14}$/',
            'role' => 'required|in:admin,employee',
        ]);

        $invitedUser = InvitedUser::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'role' => $request->role,
            'invited_by' => Auth::id(),
        ]);

        // Send WhatsApp invitation
        if (!empty($invitedUser->phone_number)) {
            try {
                SendWhatsAppInvitation::dispatchSync(
                    $invitedUser->phone_number,
                    $invitedUser->name,
                    Auth::user()->name,
                    $request->role
                );
            } catch (\Exception $e) {
                \Log::warning('Failed to send WhatsApp invitation: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User invitation sent successfully!');
    }

    public function destroy(InvitedUser $invitation)
    {
        $invitation->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'Invitation removed successfully!');
    }

    public function promoteToAdmin(User $user)
    {
        if ($user->role === 'employee') {
            $user->update(['role' => 'admin']);
            return redirect()->back()->with('success', 'User promoted to Admin successfully!');
        }

        return redirect()->back()->with('error', 'Cannot promote this user.');
    }

    public function demoteToEmployee(User $user)
    {
        if ($user->role === 'admin') {
            $user->update(['role' => 'employee']);
            return redirect()->back()->with('success', 'User demoted to Employee successfully!');
        }

        return redirect()->back()->with('error', 'Cannot demote this user.');
    }

    public function deleteUser(User $user)
    {
        // Only super admin can delete users
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Only Super Admin can delete users.');
        }

        // Prevent deleting yourself
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        // Prevent deleting other super admins
        if ($user->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Cannot delete Super Admin accounts.');
        }

        $userName = $user->name;

        // Delete user
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$userName}' has been deleted successfully!");
    }

    public function edit(User $user)
    {
        // Prevent editing super admin by non-super admins
        if ($user->isSuperAdmin() && !Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // Prevent editing super admin by non-super admins
        if ($user->isSuperAdmin() && !Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent super admin from being demoted
        if ($user->isSuperAdmin() && $request->role !== 'super_admin') {
            return redirect()->back()->with('error', 'Cannot change Super Admin role.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|regex:/^\+[1-9]\d{1,14}$/',
            'role' => 'required|in:super_admin,admin,employee',
            'preferred_channel' => 'required|in:whatsapp,in_app',
        ]);

        // Only super admin can change roles to super_admin
        if ($validated['role'] === 'super_admin' && !Auth::user()->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Only Super Admin can assign Super Admin role.');
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }
}
