<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcosystemLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'url',
        'category',
        'type',
        'is_official',
        'verification_status',
        'verified_at',
        'verified_by',
        'badge',
        'description',
        'icon',
        'is_featured',
        'is_active',
        'is_public',
        'sort_order',
        'tags',
        'notes',
        'health_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_official' => 'boolean',
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'sort_order' => 'integer',
        'verified_at' => 'datetime',
    ];

    protected $appends = [
        'domain',
        'is_verified',
        'is_review_recommended',
        'verification_badge_class',
    ];

    /**
     * Get clean domain/host name for display (e.g. sbl.com.bd).
     */
    public function getDomainAttribute(): string
    {
        if (empty($this->url)) {
            return '';
        }

        $host = parse_url($this->url, PHP_URL_HOST);
        if (!$host) {
            // Handle relative or protocol-less URLs
            $parsed = parse_url('https://' . ltrim($this->url, '/'), PHP_URL_HOST);
            $host = $parsed ?: $this->url;
        }

        return preg_replace('/^www\./i', '', $host);
    }

    /**
     * Strictly check if resource is verified and official.
     */
    public function getIsVerifiedAttribute(): bool
    {
        return (bool) $this->is_official && $this->verification_status === 'verified';
    }

    /**
     * Check if verification is older than 180 days or requires review.
     */
    public function getIsReviewRecommendedAttribute(): bool
    {
        if ($this->verification_status === 'needs_review') {
            return true;
        }

        if ($this->verification_status === 'verified' && $this->verified_at) {
            return $this->verified_at->lt(Carbon::now()->subDays(180));
        }

        return false;
    }

    /**
     * CSS classes for verification badge.
     */
    public function getVerificationBadgeClassAttribute(): string
    {
        if ($this->is_official && $this->verification_status === 'verified') {
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        }
        if ($this->verification_status === 'verified') {
            return 'bg-blue-50 text-blue-700 border-blue-200';
        }
        if ($this->verification_status === 'needs_review' || $this->is_review_recommended) {
            return 'bg-amber-50 text-amber-700 border-amber-200';
        }
        if ($this->verification_status === 'inactive' || !$this->is_active) {
            return 'bg-slate-100 text-slate-500 border-slate-200';
        }
        return 'bg-slate-50 text-slate-600 border-slate-200';
    }

    /**
     * Normalize URL for clean storage and duplicate comparison.
     */
    public static function normalizeUrl(?string $url): string
    {
        if (!$url) return '';
        $url = trim($url);
        if (!preg_match('~^(?:f|ht)tps?://~i', $url) && !str_starts_with($url, '/')) {
            $url = 'https://' . $url;
        }
        $parts = parse_url($url);
        if (!$parts || empty($parts['host'])) {
            return $url;
        }
        $scheme = strtolower($parts['scheme'] ?? 'https');
        $host = strtolower($parts['host']);
        $path = $parts['path'] ?? '/';
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        return "{$scheme}://{$host}{$path}{$query}";
    }
}

