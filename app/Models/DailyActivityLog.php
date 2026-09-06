<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'calls',
        'messenger_contacts',
        'whatsapp_contacts',
        'new_leads',
        'follow_ups',
        'presentations',
        'meetings',
        'conversions',
        'product_sales_count',
        'received_income',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'received_income' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

