<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingResource extends Model
{
    use HasFactory;

    protected $table = 'marketing_resources';

    protected $fillable = [
        'title',
        'category',
        'file_type',
        'file_url',
        'file_size',
        'badge',
        'description',
        'sort_order',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * File type icon or visual badge.
     */
    public function getIconAttribute(): string
    {
        return match ($this->file_type) {
            'pdf' => '📄',
            'image' => '🖼️',
            'presentation' => '📊',
            'doc', 'document' => '📝',
            'spreadsheet', 'excel' => '📈',
            'video' => '🎬',
            default => '🔗',
        };
    }
}
