<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Where an ajánlatkérés stands in the shop's handling. New and in-progress
 * ones count as open and show as a badge in the admin menu.
 */
enum QuoteRequestStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Quoted = 'quoted';
    case Won = 'won';
    case Lost = 'lost';

    /**
     * @return list<self>
     */
    public static function open(): array
    {
        return [self::New, self::InProgress];
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'Új',
            self::InProgress => 'Folyamatban',
            self::Quoted => 'Ajánlat elküldve',
            self::Won => 'Megrendelve',
            self::Lost => 'Elutasítva',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'warning',
            self::InProgress => 'info',
            self::Quoted => 'primary',
            self::Won => 'success',
            self::Lost => 'danger',
        };
    }
}
