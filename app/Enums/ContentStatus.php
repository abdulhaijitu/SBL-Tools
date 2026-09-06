<?php

namespace App\Enums;

enum ContentStatus: string
{
    case IDEA = 'Idea';
    case PLANNED = 'Planned';
    case DESIGN = 'Design';
    case READY = 'Ready';
    case PUBLISHED = 'Published';
    case CANCELLED = 'Cancelled';

    public function badgeClasses(): string
    {
        return match ($this) {
            self::IDEA => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::PLANNED => 'bg-blue-50 text-blue-700 border-blue-200',
            self::DESIGN => 'bg-amber-50 text-amber-700 border-amber-200',
            self::READY => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::PUBLISHED => 'bg-green-50 text-green-700 border-green-200',
            self::CANCELLED => 'bg-zinc-100 text-zinc-600 border-zinc-200',
        };
    }
}

