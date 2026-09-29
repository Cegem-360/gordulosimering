<?php

declare(strict_types=1);

namespace App\Services;

/**
 * A termék nevéből és ERP-s méretéből kiolvasható szűrési adatok: a
 * „d×D×B" méret számai, a név elején álló márka és a névben szereplő anyag.
 * Kételemű méretnél csak a belső átmérő megbízható, mert a második szám
 * termékenként mást jelent (vastagság vagy külső átmérő).
 */
final class ProductAttributeExtractor
{
    /**
     * @var array<int, string>
     */
    public const array COLUMNS = ['inner_diameter', 'outer_diameter', 'width', 'brand', 'material'];

    /**
     * @var array<int, string>
     */
    private const array BRANDS = [
        'SKF', 'KOYO', 'INA', 'FAG', 'ZKL', 'NTN', 'TIMKEN', 'NSK', 'IKO', 'SNR', 'CORTECO', 'SIMRIT',
        'SEEGER', 'LOCTITE', 'NORMA', 'TENTE', 'EZO', 'NACHI', 'REXROTH', 'HIWIN', 'BETA', 'BAHCO', 'AMES',
        'NILOS', 'BECO', 'KS', 'WSW', 'STIEBER', 'DURACELL',
    ];

    /**
     * Regex alternative => a szűrőben megjelenő anyag; a lista sorrendje dönt.
     *
     * @var array<string, string>
     */
    private const array MATERIALS = [
        'NBR' => 'NBR',
        'VITON|FKM' => 'FKM (Viton)',
        'EPDM' => 'EPDM',
        'PTFE' => 'PTFE',
        'rozsdamentes' => 'Rozsdamentes',
    ];

    /**
     * @return array{inner_diameter: ?float, outer_diameter: ?float, width: ?float, brand: ?string, material: ?string}
     */
    public function extract(?string $name, ?string $size): array
    {
        return [
            ...$this->dimensions($size),
            'brand' => $this->brand($name),
            'material' => $this->material($name),
        ];
    }

    /**
     * @return array{inner_diameter: ?float, outer_diameter: ?float, width: ?float}
     */
    private function dimensions(?string $size): array
    {
        $number = '(\d+(?:[.,]\d+)?)';

        if (preg_match("/^\\s*{$number}\\s*x\\s*{$number}(?:\\s*x\\s*{$number})?/iu", (string) $size, $matches) !== 1) {
            return ['inner_diameter' => null, 'outer_diameter' => null, 'width' => null];
        }

        $hasThreeParts = isset($matches[3]);

        return [
            'inner_diameter' => $this->toNumber($matches[1]),
            'outer_diameter' => $hasThreeParts ? $this->toNumber($matches[2]) : null,
            'width' => $hasThreeParts ? $this->toNumber($matches[3]) : null,
        ];
    }

    private function brand(?string $name): ?string
    {
        $firstWord = preg_split('/[\s,]+/u', mb_trim((string) $name), 2)[0] ?? '';
        $candidate = mb_strtoupper(explode('/', $firstWord)[0]);

        return in_array($candidate, self::BRANDS, true) ? $candidate : null;
    }

    private function material(?string $name): ?string
    {
        foreach (self::MATERIALS as $pattern => $material) {
            if (preg_match("/(?<![\\p{L}\\d])(?:{$pattern})(?!\\p{L})/iu", (string) $name) === 1) {
                return $material;
            }
        }

        return null;
    }

    private function toNumber(string $value): float
    {
        return (float) str_replace(',', '.', $value);
    }
}
