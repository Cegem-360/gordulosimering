<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A termékek minden adatbázis-oszlopa és kategóriái CSV-be, nyers (tárolt)
 * értékekkel. Pontosvesszős, UTF-8 BOM-os fájl, hogy a magyar Excel
 * közvetlenül megnyissa.
 */
final class ProductCsvExporter
{
    private const string DELIMITER = ';';

    /**
     * @param  Builder<Product>  $query
     */
    public function download(Builder $query, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'wb');
            $this->write($output, $query);
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  resource  $handle
     * @param  Builder<Product>  $query
     * @return int Az exportált termékek száma.
     */
    public function write($handle, Builder $query): int
    {
        $columns = Schema::getColumnListing((new Product())->getTable());

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, [...array_map($this->label(...), $columns), $this->label('categories')], self::DELIMITER, escape: '');

        $count = 0;

        $query->with('categories:id,name')->lazyById(1000)->each(function (Product $product) use ($handle, $columns, &$count): void {
            $values = array_map(fn (string $column): mixed => $product->getRawOriginal($column), $columns);
            $values[] = $product->categories->pluck('name')->implode(', ');

            fputcsv($handle, $values, self::DELIMITER, escape: '');
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
