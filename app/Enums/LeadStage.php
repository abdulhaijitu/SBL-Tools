<?php

namespace App\Enums;

enum LeadStage: string
{
    case NEW = 'new';
    case CONTACTED = 'contacted';
    case INTERESTED = 'interested';
    case QUALIFIED = 'qualified';
    case PRESENTATION = 'presentation';
    case FOLLOW_UP = 'follow_up';
    case DECISION = 'decision';
    case CONVERTED = 'converted';
    case NOT_NOW = 'not_now';
    case LOST = 'lost';
    case NOT_SUITABLE = 'not_suitable';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::CONTACTED => 'Contacted',
            self::INTERESTED => 'Interested',
            self::QUALIFIED => 'Qualified',
            self::PRESENTATION => 'Presentation',
            self::FOLLOW_UP => 'Follow-up',
            self::DECISION => 'Decision',
            self::CONVERTED => 'Converted',
            self::NOT_NOW => 'Not Now',
            self::LOST => 'Lost',
            self::NOT_SUITABLE => 'Not Suitable',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::NEW => 'bg-blue-50 text-blue-700 border-blue-200',
            self::CONTACTED => 'bg-sky-50 text-sky-700 border-sky-200',
            self::INTERESTED => 'bg-amber-50 text-amber-700 border-amber-200',
            self::QUALIFIED => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::PRESENTATION => 'bg-purple-50 text-purple-700 border-purple-200',
            self::FOLLOW_UP => 'bg-orange-50 text-orange-700 border-orange-200',
            self::DECISION => 'bg-yellow-50 text-yellow-800 border-yellow-200',
            self::CONVERTED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::NOT_NOW => 'bg-gray-100 text-gray-700 border-gray-200',
            self::LOST => 'bg-rose-50 text-rose-700 border-rose-200',
            self::NOT_SUITABLE => 'bg-zinc-100 text-zinc-600 border-zinc-200',
        };
    }

    public static function activePipelineStages(): array
    {
        return [
            self::NEW,
            self::CONTACTED,
            self::INTERESTED,
            self::QUALIFIED,
            self::PRESENTATION,
            self::FOLLOW_UP,
            self::DECISION,
            self::CONVERTED,
        ];
    }
}

