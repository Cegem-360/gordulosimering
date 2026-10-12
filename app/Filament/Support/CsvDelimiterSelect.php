<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Enums\CsvDelimiter;
use Filament\Forms\Components\Select;

final class CsvDelimiterSelect
{
    /**
     * Az export elválasztójának választója; a `delimiter` állapot CsvDelimiter.
     */
    public static function make(): Select
    {
        return Select::make('delimiter')
            ->label('Elválasztó')
            ->options(CsvDelimiter::class)
            ->enum(CsvDelimiter::class)
            ->default(CsvDelimiter::Semicolon)
            ->selectablePlaceholder(false)
            ->required();
    }
}
