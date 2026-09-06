<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'binary_node_id',
        'investment_plan_id',
        'plan_name',
        'amount',
        'point_value',
        'status',
        'investment_date',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'point_value' => 'decimal:2',
        'investment_date' => 'date',
    ];

    public function binaryNode(): BelongsTo
    {
        return $this->belongsTo(BinaryNode::class, 'binary_node_id');
    }

    public function investmentPlan(): BelongsTo
    {
        return $this->belongsTo(InvestmentPlan::class, 'investment_plan_id');
    }
}
