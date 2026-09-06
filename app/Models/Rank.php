<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'order',
        'requirement_text',
        'incentive_amount',
        'active',
    ];

    protected $casts = [
        'incentive_amount' => 'decimal:2',
        'active' => 'boolean',
        'order' => 'integer',
    ];
}
