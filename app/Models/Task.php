<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\TaskType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'related_lead_id',
        'related_customer_id',
        'related_member_id',
        'user_id',
        'due_at',
        'priority',
        'status',
        'notes',
        'completed_at',
        'outcome',
        'next_action',
        'next_action_at',
    ];

    protected $casts = [
        'type' => TaskType::class,
        'priority' => TaskPriority::class,
        'status' => TaskStatus::class,
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'next_action_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'related_lead_id');
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->where('status', '!=', TaskStatus::COMPLETED->value)
            ->where('status', '!=', TaskStatus::CANCELLED->value)
            ->whereDate('due_at', today());
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', '!=', TaskStatus::COMPLETED->value)
            ->where('status', '!=', TaskStatus::CANCELLED->value)
            ->where('due_at', '<', now());
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [TaskStatus::PENDING->value, TaskStatus::IN_PROGRESS->value]);
    }
}

