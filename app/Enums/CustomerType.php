<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Who places the order at checkout. A company has to give its name and VAT
 * number; a private person does not.
 */
enum CustomerType: string implements HasLabel
{
    case Private = 'private';
    case Company = 'company';

    public function getLabel(): string
    {
        return match ($this) {
            self::Private => 'Magánszemély',
            self::Company => 'Cég',
        };
    }
}
