<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'preferred_channel',
        'dark_mode',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isEmployee()
    {
        return $this->role === 'employee';
    }

    public function canManageUsers()
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function canInviteUsers()
    {
        return $this->role === 'super_admin';
    }

    public function canCreateTasks()
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function canManageTasks()
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function invitedUsers()
    {
        return $this->hasMany(InvitedUser::class, 'invited_by');
    }

    public function routeNotificationForTwilio()
    {
        return $this->phone;
    }

    public function routeNotificationForWhatsapp()
    {
        // Return phone with country code (e.g., +263783017279)
        // For Twilio, the WhatsappChannel will add the 'whatsapp:' prefix
        return $this->phone;
    }
}
