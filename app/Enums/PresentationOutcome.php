<?php

namespace App\Enums;

enum PresentationOutcome: string
{
    case HOT = 'Hot';
    case WARM = 'Warm';
    case COLD = 'Cold';
    case CONVERTED = 'Converted';
    case NOT_INTERESTED = 'Not Interested';

    public function badgeClasses(): string
    {
        return match ($this) {
            self::HOT => 'bg-red-50 text-red-700 border-red-200',
            self::WARM => 'bg-orange-50 text-orange-700 border-orange-200',
            self::COLD => 'bg-slate-50 text-slate-700 border-slate-200',
            self::CONVERTED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::NOT_INTERESTED => 'bg-zinc-100 text-zinc-600 border-zinc-200',
        };
    }
}

