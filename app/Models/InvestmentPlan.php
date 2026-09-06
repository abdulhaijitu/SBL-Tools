<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_amount',
        'max_amount',
        'website_fee',
        'weekly_return_percent',
        'duration_weeks',
        'crowdfunding_limit',
        'lifetime_profit_sharing',
        'features',
        'active',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'website_fee' => 'decimal:2',
        'weekly_return_percent' => 'decimal:2',
        'crowdfunding_limit' => 'decimal:2',
        'features' => 'array',
        'active' => 'boolean',
    ];
}
