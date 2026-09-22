<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasLabel
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case ONHOLD = 'on-hold';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case FAILED = 'failed';
    case TRASH = 'trash';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Feldolgozásra vár',
            self::PROCESSING => 'Feldolgozás alatt',
            self::ONHOLD => 'Várakoztatva',
            self::COMPLETED => 'Teljesítve',
            self::CANCELLED => 'Törölve',
            self::REFUNDED => 'Visszatérítve',
            self::FAILED => 'Sikertelen',
            self::TRASH => 'Kukában',
        };
    }
}
