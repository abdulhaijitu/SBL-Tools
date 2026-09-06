<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BinaryNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'member_name',
        'member_code',
        'phone',
        'email',
        'parent_id',
        'sponsor_id',
        'position',
        'package_name',
        'point_value',
        'left_count',
        'right_count',
        'left_bv',
        'right_bv',
        'carry_left',
        'carry_right',
        'matched_pairs',
        'rank_name',
        'avatar',
        'is_active',
        'joined_at',
    ];

    protected $casts = [
        'point_value' => 'decimal:2',
        'left_bv' => 'decimal:2',
        'right_bv' => 'decimal:2',
        'carry_left' => 'decimal:2',
        'carry_right' => 'decimal:2',
        'left_count' => 'integer',
        'right_count' => 'integer',
        'matched_pairs' => 'integer',
        'is_active' => 'boolean',
        'joined_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BinaryNode::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(BinaryNode::class, 'parent_id');
    }

    public function leftChild(): HasOne
    {
        return $this->hasOne(BinaryNode::class, 'parent_id')->where('position', 'left');
    }

    public function rightChild(): HasOne
    {
        return $this->hasOne(BinaryNode::class, 'parent_id')->where('position', 'right');
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(BinaryNode::class, 'sponsor_id');
    }

    public function sponsoredMembers(): HasMany
    {
        return $this->hasMany(BinaryNode::class, 'sponsor_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getWeakerLegAttribute(): string
    {
        return $this->carry_left <= $this->carry_right ? 'left' : 'right';
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
