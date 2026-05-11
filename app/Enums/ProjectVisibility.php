<?php

namespace App\Enums;

enum ProjectVisibility: string
{
    case PRIVATE = 'private';
    case PUBLIC = 'public';
    case TEAM = 'team';

    /**
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
