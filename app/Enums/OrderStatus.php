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

    /**
     * What the customer is told when an order moves to this status, or null
     * for the internal statuses (back to pending, moved to trash), which send
     * the customer nothing.
     */
    public function customerMessage(): ?string
    {
        return match ($this) {
            self::PROCESSING => 'Rendelését feldolgozzuk. Hamarosan értesítjük a teljesítésről.',
            self::ONHOLD => 'Rendelését átmenetileg várakoztatjuk. Kollégánk hamarosan felveszi Önnel a kapcsolatot.',
            self::COMPLETED => 'Rendelését teljesítettük. Köszönjük, hogy minket választott!',
            self::CANCELLED => 'Rendelését töröltük. Ha kérdése van, kérjük, keressen minket.',
            self::REFUNDED => 'Rendelésének összegét visszatérítettük.',
            self::FAILED => 'Rendelését sajnos nem sikerült teljesíteni. Kérjük, vegye fel velünk a kapcsolatot.',
            self::PENDING, self::TRASH => null,
        };
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
