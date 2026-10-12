<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Az export elválasztója. A tabulátoros fájl `.txt` kiterjesztést kap:
 * az Excel is így menti, és a Filament import csak csv/txt fájlt fogad.
 */
enum CsvDelimiter: string implements HasLabel
{
    case Semicolon = 'semicolon';
    case Comma = 'comma';
    case Tab = 'tab';

    public function getLabel(): string
    {
        return match ($this) {
            self::Semicolon => 'Pontosvessző (;) – magyar Excel',
            self::Comma => 'Vessző (,)',
            self::Tab => 'Tabulátor (TSV, .txt)',
        };
    }

    public function character(): string
    {
        return match ($this) {
            self::Semicolon => ';',
            self::Comma => ',',
            self::Tab => "\t",
        };
    }

    public function extension(): string
    {
        return $this === self::Tab ? 'txt' : 'csv';
    }

    public function contentType(): string
    {
        return $this === self::Tab ? 'text/tab-separated-values' : 'text/csv';
    }
}
