<?php

namespace App\Models;

use App\Enums\LeadStage;
use App\Enums\LeadTemperature;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'mobile',
        'whatsapp',
        'email',
        'facebook_url',
        'location',
        'profession_or_business',
        'lead_source_id',
        'lead_source_detail',
        'interest_types',
        'lead_tag',
        'stage',
        'temperature',
        'score',
        'is_manual_score',
        'budget_range',
        'decision_timeline',
        'owner_user_id',
        'next_action_type',
        'next_action_at',
        'last_contact_at',
        'converted_at',
        'notes',
    ];

    protected $casts = [
        'stage' => LeadStage::class,
        'temperature' => LeadTemperature::class,
        'interest_types' => 'array',
        'is_manual_score' => 'boolean',
        'score' => 'integer',
        'next_action_at' => 'datetime',
        'last_contact_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function interests(): HasMany
    {
        return $this->hasMany(LeadInterest::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->orderBy('performed_at', 'desc');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'related_lead_id')->orderBy('due_at', 'asc');
    }

    public function presentations(): HasMany
    {
        return $this->hasMany(Presentation::class)->orderBy('date_time', 'desc');
    }

    /**
     * Determine if the lead is currently stale based on rules:
     * - 7 days for Hot/Warm without activity
     * - 14 days for Cold without activity
     */
    public function getIsStaleAttribute(): bool
    {
        if (in_array($this->stage, [LeadStage::CONVERTED, LeadStage::LOST, LeadStage::NOT_SUITABLE])) {
            return false;
        }

        $lastActivity = $this->last_contact_at ?? $this->created_at;
        if (! $lastActivity) {
            return false;
        }

        $daysSince = Carbon::parse($lastActivity)->diffInDays(now());

        if (in_array($this->temperature, [LeadTemperature::HOT, LeadTemperature::WARM])) {
            return $daysSince >= 7;
        }

        return $daysSince >= 14;
    }

    /**
     * Check if next action is overdue
     */
    public function getIsNextActionOverdueAttribute(): bool
    {
        if (! $this->next_action_at) {
            return false;
        }

        return $this->next_action_at->isPast() && ! in_array($this->stage, [LeadStage::CONVERTED, LeadStage::LOST, LeadStage::NOT_SUITABLE]);
    }

    /**
     * Check if lead needs a next action
     */
    public function getNeedsNextActionAttribute(): bool
    {
        if (in_array($this->stage, [LeadStage::CONVERTED, LeadStage::LOST, LeadStage::NOT_SUITABLE])) {
            return false;
        }

        return empty($this->next_action_at);
    }

    /**
     * Calculate score and temperature based on rules if not manual override
     */
    public function calculateScoreAndTemperature(): void
    {
        if ($this->is_manual_score) {
            return;
        }

        $score = 0;

        // Strong interest (+20)
        if (! empty($this->interest_types) && count($this->interest_types) > 0) {
            $score += 20;
        }

        // Budget available (+20)
        if (! empty($this->budget_range) && $this->budget_range !== 'No Budget') {
            $score += 20;
        }

        // Presentation attended / has presentation (+20)
        if (($this->exists && $this->presentations()->exists()) || $this->stage === LeadStage::PRESENTATION) {
            $score += 20;
        }

        // Decision timeline <= 30 days (+15)
        if (! empty($this->decision_timeline) && in_array($this->decision_timeline, ['Immediate', 'Within 7 Days', 'Within 15 Days', 'Within 30 Days'])) {
            $score += 15;
        }

        // Responds regularly / has recent activity (+10)
        if ($this->last_contact_at && $this->last_contact_at->diffInDays(now()) <= 7) {
            $score += 10;
        }

        // Follow-up interaction (+15)
        if ($this->stage === LeadStage::FOLLOW_UP || $this->stage === LeadStage::DECISION) {
            $score += 15;
        }

        $score = min(100, max(0, $score));
        $this->score = $score;

        if ($this->isStale) {
            $this->temperature = LeadTemperature::STALE;
        } elseif ($score >= 80) {
            $this->temperature = LeadTemperature::HOT;
        } elseif ($score >= 50) {
            $this->temperature = LeadTemperature::WARM;
        } else {
            $this->temperature = LeadTemperature::COLD;
        }
    }

    // Scopes
    public function scopeActivePipeline(Builder $query): Builder
    {
        return $query->whereNotIn('stage', [
            LeadStage::CONVERTED->value,
            LeadStage::LOST->value,
            LeadStage::NOT_SUITABLE->value,
        ]);
    }

    public function scopeOverdueFollowups(Builder $query): Builder
    {
        return $query->activePipeline()
            ->whereNotNull('next_action_at')
            ->where('next_action_at', '<', now());
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->activePipeline()
            ->whereNotNull('next_action_at')
            ->whereDate('next_action_at', today());
    }

    public function scopeNeedsNextAction(Builder $query): Builder
    {
        return $query->activePipeline()
            ->whereNull('next_action_at');
    }
}
