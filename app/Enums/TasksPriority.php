<?php

namespace App\Enums;

enum TasksPriority: string
{
    case URGENT = 'urgent';
    case HIGH = 'high';
    case NORMAL = 'normal';
    case LOW = 'low';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
