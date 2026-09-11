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
        'short_title',
        'category',
        'resource_type',
        'file_type',
        'file_url',
        'thumbnail_url',
        'file_size',
        'badge',
        'version',
        'source',
        'is_official',
        'verification_status',
        'verified_at',
        'verified_by',
        'issue_date',
        'expiry_date',
        'issued_by',
        'tags',
        'language',
        'is_featured',
        'is_counseling_toolkit',
        'is_public',
        'is_downloadable',
        'is_shareable',
        'status',
        'description',
        'notes',
        'sort_order',
        'download_count',
        'view_count',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_official' => 'boolean',
        'is_featured' => 'boolean',
        'is_counseling_toolkit' => 'boolean',
        'is_public' => 'boolean',
        'is_downloadable' => 'boolean',
        'is_shareable' => 'boolean',
        'sort_order' => 'integer',
        'download_count' => 'integer',
        'view_count' => 'integer',
        'verified_at' => 'datetime',
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if resource is explicitly verified as official SBL asset or verified document.
     */
    public function getIsVerifiedAttribute(): bool
    {
        return $this->is_official && in_array($this->verification_status, ['official_verified', 'verified_document']);
    }

    /**
     * Verification Badge Class for styling.
     */
    public function getVerificationBadgeClassAttribute(): string
    {
        if ($this->is_official && $this->verification_status === 'official_verified') {
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        }

        if ($this->verification_status === 'verified_document' || $this->verification_status === 'government_document') {
            return 'bg-blue-50 text-blue-700 border-blue-200';
        }

        if ($this->verification_status === 'sbl_provided') {
            return 'bg-teal-50 text-teal-700 border-teal-200';
        }

        if ($this->verification_status === 'internal_marketing') {
            return 'bg-indigo-50 text-indigo-700 border-indigo-200';
        }

        if ($this->verification_status === 'needs_review' || $this->is_review_recommended) {
            return 'bg-amber-50 text-amber-700 border-amber-200';
        }

        if ($this->verification_status === 'expired' || $this->is_expired) {
            return 'bg-rose-50 text-rose-700 border-rose-200';
        }

        return 'bg-slate-50 text-slate-600 border-slate-200';
    }

    /**
     * Verification Badge Label.
     */
    public function getVerificationBadgeLabelAttribute(): string
    {
        if ($this->is_official && $this->verification_status === 'official_verified') {
            return 'Official SBL Asset';
        }

        if ($this->verification_status === 'government_document') {
            return 'Govt. Document';
        }

        if ($this->verification_status === 'verified_document') {
            return 'Verified Document';
        }

        if ($this->verification_status === 'sbl_provided') {
            return 'SBL Provided';
        }

        if ($this->verification_status === 'internal_marketing') {
            return 'Internal Marketing';
        }

        if ($this->verification_status === 'needs_review' || $this->is_review_recommended) {
            return 'Needs Review';
        }

        if ($this->verification_status === 'expired' || $this->is_expired) {
            return 'Expired / Outdated';
        }

        return 'Needs Verification';
    }

    /**
     * Is the resource past its review date or expiry date?
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if review is recommended (> 180 days since verification or update).
     */
    public function getIsReviewRecommendedAttribute(): bool
    {
        if ($this->status === 'review_recommended' || $this->verification_status === 'needs_review') {
            return true;
        }

        if ($this->verified_at && $this->verified_at->diffInDays(now()) > 180) {
            return true;
        }

        return false;
    }

    /**
     * File type icon or visual indicator.
     */
    public function getFileIconAttribute(): string
    {
        return match ($this->file_type) {
            'pdf' => '📄',
            'image', 'png', 'jpg', 'jpeg' => '🖼️',
            'presentation', 'keynote', 'slides' => '📊',
            'doc', 'docx', 'document' => '📝',
            'spreadsheet', 'excel', 'sheet' => '📈',
            'video', 'mp4' => '🎬',
            'zip', 'archive' => '📦',
            default => '📎',
        };
    }

    /**
     * Determine if preview is supported directly in browser modal.
     */
    public function getCanPreviewAttribute(): bool
    {
        return in_array($this->file_type, ['image', 'pdf', 'presentation', 'doc']) ||
            str_ends_with(strtolower($this->file_url), '.png') ||
            str_ends_with(strtolower($this->file_url), '.jpg') ||
            str_ends_with(strtolower($this->file_url), '.jpeg') ||
            str_ends_with(strtolower($this->file_url), '.pdf') ||
            str_contains($this->file_url, 'docs.google.com');
    }
}
