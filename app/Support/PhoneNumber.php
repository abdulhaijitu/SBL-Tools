<?php

namespace App\Support;

final class PhoneNumber
{
    public static function whatsapp(?string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number ?? '');
        if (str_starts_with($digits, '00')) $digits = substr($digits, 2);
        // Existing local records use Bangladesh mobile numbers.
        return preg_match('/^01[3-9]\d{8}$/', $digits) ? '88'.$digits : $digits;
    }
}
