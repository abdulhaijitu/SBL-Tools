<?php

namespace App\Enums;

enum PresentationType: string
{
    case ONLINE = 'Online';
    case OFFLINE = 'Offline';
    case GROUP = 'Group';
    case ONE_TO_ONE = '1-to-1';
}

