<?php

namespace App\Enums;

enum LeadTemperature: string
{
    case HOT = 'hot';
    case WARM = 'warm';
    case COLD = 'cold';
    case STALE = 'stale';

    public function label(): string
    {
        return match ($this) {
            self::HOT => 'Hot',
            self::WARM => 'Warm',
            self::COLD => 'Cold',
            self::STALE => 'Stale',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::HOT => 'bg-red-50 text-red-700 border-red-200',
            self::WARM => 'bg-orange-50 text-orange-700 border-orange-200',
            self::COLD => 'bg-slate-100 text-slate-700 border-slate-200',
            self::STALE => 'bg-purple-50 text-purple-700 border-purple-200',
        };
    }
}

