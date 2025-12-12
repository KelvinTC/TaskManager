<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvitedUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'phone_number',
        'role',
        'invited_by',
        'registered',
    ];

    protected $casts = [
        'registered' => 'boolean',
    ];

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function scopeNotRegistered($query)
    {
        return $query->where('registered', false);
    }

    public function markAsRegistered()
    {
        $this->update(['registered' => true]);
    }

    public function routeNotificationForWhatsapp()
    {
        return $this->phone_number;
    }
}
