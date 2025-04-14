<?php

namespace App\Enums;

enum ClaimStatus: string
{
    case YES = 'yes';
    case NO = 'no';

    public static function default(): string
    {
        return self::NO->value;
    }
}
