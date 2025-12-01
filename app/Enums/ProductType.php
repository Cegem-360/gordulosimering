<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductType: string
{
    case VARIABLE = 'variable';
    case SIMPLE = 'simple';
    case GROUPED = 'grouped';
    case EXTERNAL = 'external';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
