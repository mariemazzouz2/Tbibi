<?php

namespace App\Enum;

enum TypeVote: string
{
    case LIKE = 'like';
    case DISLIKE = 'dislike';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
