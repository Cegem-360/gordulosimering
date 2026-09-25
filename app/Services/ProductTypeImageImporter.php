<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use RuntimeException;

/**
 * Típusképeket rendel a termékekhez a termékféleség alapján.
 *
 * A termékkód szerinti egyedi kép mindig erősebb: ez az importer csak azokat a
 * termékeket tölti fel, amelyeknek nincs képük, illetve azokat, amelyek korábban
 * épp egy típusképet kaptak – így a leképezés később módosítható anélkül, hogy a
 * kézzel vagy a termek_kepek.tsv-ből kapott képeket felülírná.
 */
final class ProductTypeImageImporter
{
    private const int BATCH_SIZE = 500;

    /**
     * Gyűjtő-termékféleségek, amelyek nem egy típust jelölnek, hanem annak
     * hiányát – egyetlen képük minden besorolatlan termékre rossz képet tenne.
     *
     * @var array<int, string>
     */
    private const array UNCLASSIFIED_VARIETIES = ['Nincs megadva'];

    /**
     * @return array{types: int, matched_types: int, products: int}
     */
    public function import(string $path, bool $dryRun = false): array
    {
        $map = $this->readTypeMap($path);
        $typeImages = array_values($map);

        $stats = ['types' => count($map), 'matched_types' => 0, 'products' => 0];

        foreach (array_chunk($map, self::BATCH_SIZE, true) as $chunk) {
            foreach ($chunk as $variety => $image) {
                $query = Product::query()
                    ->where('product_variety', $variety)
                    ->where(fn ($inner) => $inner->whereNull('featured_image')->orWhereIn('featured_image', $typeImages))
                    ->where(fn ($inner) => $inner->whereNull('featured_image')->orWhere('featured_image', '!=', $image));

                $count = (clone $query)->count();

                if ($count === 0) {
                    continue;
                }

                $stats['matched_types']++;
                $stats['products'] += $count;

                if (! $dryRun) {
                    $query->update(['featured_image' => $image]);
                }
            }
        }

        return $stats;
    }

    /**
     * Beolvassa a termékféleség => kép URL leképezést. Az első sor fejléc, a
     * későbbi duplikált termékféleséget és a gyűjtő-termékféleségeket
     * figyelmen kívül hagyjuk.
     *
     * @return array<string, string>
     */
    private function readTypeMap(string $path): array
    {
        $handle = fopen($path, 'r');
        throw_if($handle === false, RuntimeException::class, 'Could not open TSV file: ' . $path);

        fgetcsv($handle, 0, "\t");

        $map = [];

        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            $variety = mb_trim($row[0] ?? '');
            $image = mb_trim($row[1] ?? '');

            if ($variety === '' || $image === '' || isset($map[$variety]) || in_array($variety, self::UNCLASSIFIED_VARIETIES, true)) {
                continue;
            }

            $map[$variety] = $image;
        }

        fclose($handle);

        return $map;
    }
}
