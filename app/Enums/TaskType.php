<?php

namespace App\Enums;

enum TaskType: string
{
    case CALL = 'Call';
    case MESSENGER = 'Messenger';
    case WHATSAPP = 'WhatsApp';
    case FOLLOW_UP = 'Follow-up';
    case PRESENTATION = 'Presentation';
    case MEETING = 'Meeting';
    case CONTENT = 'Content';
    case PRODUCT_FOLLOW_UP = 'Product Follow-up';
    case PAYMENT_FOLLOW_UP = 'Payment Follow-up';
    case MEMBER_SUPPORT = 'Member Support';
    case TRAINING = 'Training';
    case OTHER = 'Other';

    public function icon(): string
    {
        return match ($this) {
            self::CALL => 'phone',
            self::MESSENGER => 'chat-bubble-left-ellipsis',
            self::WHATSAPP => 'chat-bubble-oval-left',
            self::FOLLOW_UP => 'arrow-path',
            self::PRESENTATION => 'presentation-chart-bar',
            self::MEETING => 'user-group',
            self::CONTENT => 'document-text',
            default => 'check-circle',
        };
    }
}

