<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'created_by',
        'status',
        'scheduled_at',
        'completed_at',
        'last_reminded_at',
        'overdue_notified_at',
        'priority',
        'visibility',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_reminded_at' => 'datetime',
            'overdue_notified_at' => 'datetime',
        ];
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOverdue($query)
    {
        return $query->where('scheduled_at', '<', now())
            ->whereNotIn('status', ['completed']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopePrivate($query)
    {
        return $query->where('visibility', 'private');
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function canBeViewedBy(User $user): bool
    {
        // Creator can always view
        if ($this->created_by === $user->id) {
            return true;
        }

        // Assigned user can always view
        if ($this->assigned_to === $user->id) {
            return true;
        }

        // Super admins can view all tasks
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Public tasks can be viewed by anyone
        if ($this->isPublic()) {
            return true;
        }

        // Private tasks can only be viewed by assigned user and creator
        return false;
    }
}
