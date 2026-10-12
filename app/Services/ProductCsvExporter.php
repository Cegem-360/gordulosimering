<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CsvDelimiter;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A termékek minden adatbázis-oszlopa és kategóriái CSV-be, nyers (tárolt)
 * értékekkel. Alapból pontosvesszős, UTF-8 BOM-os fájl, hogy a magyar Excel
 * közvetlenül megnyissa.
 */
final class ProductCsvExporter
{
    /**
     * @param  Builder<Product>  $query
     * @param  string  $basename  A fájlnév kiterjesztés nélkül; azt az elválasztó adja.
     */
    public function download(Builder $query, string $basename, CsvDelimiter $delimiter = CsvDelimiter::Semicolon): StreamedResponse
    {
        return response()->streamDownload(function () use ($query, $delimiter): void {
            $output = fopen('php://output', 'wb');
            $this->write($output, $query, $delimiter);
            fclose($output);
        }, $basename . '.' . $delimiter->extension(), ['Content-Type' => $delimiter->contentType() . '; charset=UTF-8']);
    }

    /**
     * @param  resource  $handle
     * @param  Builder<Product>  $query
     * @return int Az exportált termékek száma.
     */
    public function write($handle, Builder $query, CsvDelimiter $delimiter = CsvDelimiter::Semicolon): int
    {
        $columns = Schema::getColumnListing((new Product())->getTable());

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, [...array_map($this->label(...), $columns), $this->label('categories')], $delimiter->character(), escape: '');

        $count = 0;

        $query->with('categories:id,name')->lazyById(1000)->each(function (Product $product) use ($handle, $columns, $delimiter, &$count): void {
            $values = array_map(fn (string $column): mixed => $product->getRawOriginal($column), $columns);
            $values[] = $product->categories->pluck('name')->implode(', ');

            fputcsv($handle, $values, $delimiter->character(), escape: '');
            $count++;
        });

        return $count;
    }

    /**
     * A magyar mezőcímke, zárójelben az oszlopnévvel, hogy az adatbázisbeli
     * mező is beazonosítható legyen.
     */
    private function label(string $column): string
    {
        $key = 'fields.' . $column;

        return Lang::has($key) ? __($key) . ' (' . $column . ')' : $column;
    }
}
