<?php

declare(strict_types=1);

namespace App\Filament\Imports\Concerns;

use App\Filament\Support\FieldLabel;
use Filament\Actions\Imports\ImportColumn;
use Illuminate\Database\Eloquent\Model;

/**
 * Oszlopok a magyar Excelből mentett CSV-hez. A Filament beépített
 * `boolean()` és `numeric()` castolása a "nem" értéket igaznak, az
 * "1 234,50" számot 123450-nek olvasná. A felismerhetetlen értéket
 * változatlanul hagyjuk, hogy a validáció hibás sorként jelezze.
 */
trait CastsHungarianValues
{
    /**
     * @param  bool  $nullable  Ha nem, az üres cella nem írja felül a tárolt értéket.
     */
    protected static function booleanColumn(string $name, bool $nullable = true): ImportColumn
    {
        return self::keepWhenBlank(
            ImportColumn::make($name)
                ->castStateUsing(fn (mixed $state): bool|string|null => match (mb_strtolower((string) $state)) {
                    '' => null,
                    '1', 'igen', 'i', 'true', 'yes', 'x' => true,
                    '0', 'nem', 'n', 'false', 'no' => false,
                    default => (string) $state,
                })
                ->rules(['nullable', 'boolean']),
            $nullable,
        );
    }

    /**
     * @param  bool  $nullable  Ha nem, az üres cella nem írja felül a tárolt értéket.
     */
    protected static function numericColumn(string $name, bool $integer = false, bool $nullable = true): ImportColumn
    {
        return self::keepWhenBlank(
            ImportColumn::make($name)
                ->castStateUsing(function (mixed $state) use ($integer): int|float|string|null {
                    $number = str_replace([' ', "\u{00A0}", ','], ['', '', '.'], (string) $state);

                    return match (true) {
                        $number === '' => null,
                        ! is_numeric($number) => (string) $state,
                        $integer && (float) $number === floor((float) $number) => (int) $number,
                        default => (float) $number,
                    };
                })
                ->rules(['nullable', $integer ? 'integer' : 'numeric']),
            $nullable,
        );
    }

    /**
     * A ProductCsvExporter fejléce ("Termékkód (product_code)"), hogy a
     * visszatöltött export oszlopai maguktól párosodjanak.
     *
     * @return list<string>
     */
    protected static function exportHeaderGuess(string $name): array
    {
        $label = FieldLabel::for($name);

        return $label === null ? [] : [$label . ' (' . $name . ')'];
    }

    private static function keepWhenBlank(ImportColumn $column, bool $nullable): ImportColumn
    {
        if ($nullable) {
            return $column;
        }

        return $column->fillRecordUsing(function (Model $record, mixed $state) use ($column): void {
            if ($state !== null) {
                $record->setAttribute($column->getName(), $state);
            }
        });
    }
}
