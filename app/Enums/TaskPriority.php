<?php

namespace App\Enums;

enum TaskPriority: string
{
    case HIGH = 'High';
    case MEDIUM = 'Medium';
    case LOW = 'Low';

    public function badgeClasses(): string
    {
        return match ($this) {
            self::HIGH => 'bg-red-50 text-red-700 border-red-200',
            self::MEDIUM => 'bg-amber-50 text-amber-700 border-amber-200',
            self::LOW => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }
}

