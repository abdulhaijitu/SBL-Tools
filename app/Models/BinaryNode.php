<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BinaryNode extends Model
{
    use \App\Models\Concerns\ScopesWorkspaceRecords;
    use HasFactory;

    protected $fillable = [
        'tree_owner_id',
        'user_id',
        'member_name',
        'member_code',
        'phone',
        'email',
        'password_plain',
        'tpin',
        'parent_id',
        'sponsor_id',
        'sponsor_name',
        'branch',        // 'LEFT' or 'RIGHT'
        'slot_number',   // 1 to 5
        'position',      // legacy fallback
        'package_name',
        'point_value',
        'contributions',
        'left_count',
        'left_target_count',
        'right_count',
        'right_target_count',
        'left_bv',
        'right_bv',
        'carry_left',
        'carry_right',
        'matched_pairs',
        'rank_name',
        'avatar',
        'is_active',
        'is_target',
        'target_date',
        'target_notes',
        'joined_at',
    ];

    protected $hidden = ['password_plain', 'tpin'];

    protected $casts = [
        'password_plain' => 'encrypted',
        'tpin' => 'encrypted',
        'slot_number' => 'integer',
        'point_value' => 'decimal:2',
        'contributions' => 'array',
        'left_bv' => 'decimal:2',
        'right_bv' => 'decimal:2',
        'carry_left' => 'decimal:2',
        'carry_right' => 'decimal:2',
        'left_count' => 'integer',
        'left_target_count' => 'integer',
        'right_count' => 'integer',
        'right_target_count' => 'integer',
        'matched_pairs' => 'integer',
        'is_active' => 'boolean',
        'is_target' => 'boolean',
        'target_date' => 'date',
        'joined_at' => 'datetime',
    ];

    public function treeOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tree_owner_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BinaryNode::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(BinaryNode::class, 'parent_id');
    }

    public function leftChildren(): HasMany
    {
        return $this->hasMany(BinaryNode::class, 'parent_id')
            ->where(function ($q) {
                $q->where('branch', 'LEFT')->orWhere('position', 'left');
            })
            ->orderBy('slot_number');
    }

    public function rightChildren(): HasMany
    {
        return $this->hasMany(BinaryNode::class, 'parent_id')
            ->where(function ($q) {
                $q->where('branch', 'RIGHT')->orWhere('position', 'right');
            })
            ->orderBy('slot_number');
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(BinaryNode::class, 'sponsor_id');
    }

    public function sponsoredMembers(): HasMany
    {
        return $this->hasMany(BinaryNode::class, 'sponsor_id');
    }

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class, 'binary_node_id');
    }

    public function getTotalInvestmentAmountAttribute(): float
    {
        if ($this->relationLoaded('investments')) {
            return (float)$this->investments->where('status', 'active')->sum('amount');
        }
        return (float)$this->investments()->where('status', 'active')->sum('amount');
    }

    public function getTotalInvestmentPvAttribute(): float
    {
        if ($this->relationLoaded('investments')) {
            return (float)$this->investments->where('status', 'active')->sum('point_value');
        }
        return (float)$this->investments()->where('status', 'active')->sum('point_value');
    }

    public function getSlotLabelAttribute(): string
    {
        if (! $this->parent_id) {
            return 'ROOT';
        }
        $b = strtoupper($this->branch ?: ($this->position === 'left' ? 'LEFT' : 'RIGHT'));
        $s = $this->slot_number ?: 1;
        return "{$b}-{$s}";
    }

    public function getWeakerLegAttribute(): string
    {
        return $this->carry_left <= $this->carry_right ? 'LEFT' : 'RIGHT';
    }

    public function getTotalTeamCountAttribute(): int
    {
        return $this->left_count + $this->right_count;
    }

    public function getTotalBvAttribute(): float
    {
        return (float)($this->left_bv + $this->right_bv);
    }
}
