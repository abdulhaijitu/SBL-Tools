<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SblContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'department',
        'contact_person',
        'phone',
        'whatsapp',
        'email',
        'available_hours',
        'description',
        'icon',
        'badge',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get clean phone number for tel: link
     */
    public function getCleanPhoneAttribute()
    {
        return preg_replace('/[^0-9+]/', '', $this->phone);
    }

    /**
     * Get clean whatsapp number for wa.me link
     */
    public function getCleanWhatsappAttribute()
    {
        $num = preg_replace('/[^0-9]/', '', $this->whatsapp ?: $this->phone);
        // If starts with 01, prepend 88
        if (str_starts_with($num, '01')) {
            $num = '88' . $num;
        }
        return $num;
    }
}
