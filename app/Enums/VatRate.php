<?php

declare(strict_types=1);

namespace App\Enums;

enum VatRate: string
{
    case Standard = 'standard';
    case Reduced18 = 'reduced_18';
    case Reduced5 = 'reduced_5';
    case Zero = 'zero';
    case Exempt = 'exempt';

    public function label(): string
    {
        return match ($this) {
            self::Standard => '27%',
            self::Reduced18 => '18%',
            self::Reduced5 => '5%',
            self::Zero => '0%',
            self::Exempt => __('VAT exempt'),
        };
    }

    public function percentage(): int
    {
        return match ($this) {
            self::Standard => 27,
            self::Reduced18 => 18,
            self::Reduced5 => 5,
            self::Zero, self::Exempt => 0,
        };
    }
}
