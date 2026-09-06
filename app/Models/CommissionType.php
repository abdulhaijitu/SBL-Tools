<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'rate_description',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
