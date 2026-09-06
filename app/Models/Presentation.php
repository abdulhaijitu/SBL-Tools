<?php

namespace App\Models;

use App\Enums\PresentationOutcome;
use App\Enums\PresentationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'date_time',
        'type',
        'topic',
        'interest_focus',
        'questions',
        'objections',
        'outcome',
        'next_follow_up_at',
        'notes',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'type' => PresentationType::class,
        'outcome' => PresentationOutcome::class,
        'next_follow_up_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

