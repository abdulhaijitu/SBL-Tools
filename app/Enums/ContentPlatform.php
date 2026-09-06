<?php

namespace App\Enums;

enum ContentPlatform: string
{
    case FACEBOOK_PROFILE = 'Facebook Profile';
    case FACEBOOK_PAGE = 'Facebook Page';
    case FACEBOOK_STORY = 'Facebook Story';
    case REEL = 'Reel';
    case MESSENGER = 'Messenger';
    case WHATSAPP = 'WhatsApp';
    case TIKTOK = 'TikTok';
    case YOUTUBE = 'YouTube';
    case OFFLINE = 'Offline';
    case OTHER = 'Other';
}

