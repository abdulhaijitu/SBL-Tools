<?php

namespace App\Models;

use App\Enums\ContentPlatform;
use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentItem extends Model
{
    use \App\Models\Concerns\ScopesWorkspaceRecords;
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'title',
        'platform',
        'content_type',
        'topic',
        'caption',
        'creative_path',
        'scheduled_at',
        'published_at',
        'status',
        'cta',
        'reach',
        'engagement',
        'inbox_count',
        'leads_generated',
        'conversions',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'status' => ContentStatus::class,
        'platform' => ContentPlatform::class,
        'reach' => 'integer',
        'engagement' => 'integer',
        'inbox_count' => 'integer',
        'leads_generated' => 'integer',
        'conversions' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}

