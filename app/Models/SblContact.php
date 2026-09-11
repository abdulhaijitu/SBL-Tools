<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SblContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'department',
        'department_en',
        'department_bn',
        'contact_person',
        'phone',
        'whatsapp',
        'email',
        'available_hours',
        'hours_en',
        'hours_bn',
        'days',
        'description',
        'description_en',
        'description_bn',
        'icon',
        'badge',
        'service_label_en',
        'service_label_bn',
        'category',
        'priority',
        'verification_status',
        'verified_at',
        'is_official',
        'is_active',
        'is_public',
        'open_time',
        'close_time',
        'is_24_hours',
        'is_primary',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_official' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'is_24_hours' => 'boolean',
        'sort_order' => 'integer',
        'priority' => 'integer',
        'verified_at' => 'datetime',
    ];

    /**
     * Get clean phone number for tel: link
     */
    public function getCleanPhoneAttribute()
    {
        return preg_replace('/[^0-9+]/', '', $this->phone);
        return preg_replace('/[^0-9+]/', '', $this->phone ?? '');
    }

    /**
     * Get clean whatsapp number for wa.me link
     */
    public function getCleanWhatsappAttribute()
    {
        $num = preg_replace('/[^0-9]/', '', $this->whatsapp ?: $this->phone);
        // If starts with 01, prepend 88
        $num = preg_replace('/[^0-9]/', '', $this->whatsapp ?: $this->phone ?: '');
        if (str_starts_with($num, '01')) {
            $num = '88' . $num;
        }
        return $num;
    }

    /**
     * Normalized display department name based on locale
     */
    public function getDepartmentNameAttribute()
    {
        return app()->getLocale() === 'bn' 
            ? ($this->department_bn ?: $this->department) 
            : ($this->department_en ?: $this->department);
    }

    /**
     * Normalized display badge / service label based on locale
     */
    public function getServiceLabelAttribute()
    {
        return app()->getLocale() === 'bn' 
            ? ($this->service_label_bn ?: $this->badge) 
            : ($this->service_label_en ?: $this->badge);
    }

    /**
     * Normalized description based on locale
     */
    public function getDescriptionTextAttribute()
    {
        return app()->getLocale() === 'bn' 
            ? ($this->description_bn ?: $this->description) 
            : ($this->description_en ?: $this->description);
    }

    /**
     * Normalized hours based on locale
     */
    public function getHoursTextAttribute()
    {
        return app()->getLocale() === 'bn' 
            ? ($this->hours_bn ?: $this->available_hours) 
            : ($this->hours_en ?: $this->available_hours);
    }
}
