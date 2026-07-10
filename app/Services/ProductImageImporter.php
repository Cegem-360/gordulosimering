<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use RuntimeException;

final class ProductImageImporter
{
    /**
     * Assigns images to products from a TSV (TERMÉKKÓD, TERMÉKNÉV, KÉP 1..3).
     *
     * A product code may be exact or a group code using "..." as a wildcard
     * (e.g. "BETA 7352B ..." covers every size variant). Codes that resolve to
     * more than $maxVariantsPerCode products are treated as too generic and
     * skipped, so a single-letter code never paints thousands of products.
     *
     * @return array{codes: int, exact: int, wildcard: int, zero: int, skipped: int, products: int, skipped_codes: array<string, int>}
     */
    public function import(string $path, int $maxVariantsPerCode = 25): array
    {
        $map = $this->readImageMap($path);

        $stats = ['codes' => count($map), 'exact' => 0, 'wildcard' => 0, 'zero' => 0, 'skipped' => 0, 'products' => 0];
        $skippedCodes = [];

        foreach ($map as $code => $images) {
            $code = (string) $code;

            $ids = Product::query()->where('product_code', $code)->pluck('id')->all();
            $isExact = $ids !== [];

            if (! $isExact) {
                $ids = Product::query()->where('product_code', 'LIKE', $this->toLikePattern($code))->pluck('id')->all();
            }

            if ($ids === []) {
                $stats['zero']++;

                continue;
            }

            if (count($ids) > $maxVariantsPerCode) {
                $stats['skipped']++;
                $skippedCodes[$code] = count($ids);

                continue;
            }

            $isExact ? $stats['exact']++ : $stats['wildcard']++;

            Product::query()->whereIn('id', $ids)->update([
                'featured_image' => $images[0],
                'images' => json_encode(array_slice($images, 1), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ]);

            $stats['products'] += count($ids);
        }

        arsort($skippedCodes);
        $stats['skipped_codes'] = $skippedCodes;

        return $stats;
    }

    /**
     * Reads the TSV into a product-code => image-URL list map (first row per
     * code wins). Rows without any image URL are ignored.
     *
     * @return array<string, array<int, string>>
     */
    private function readImageMap(string $path): array
    {
        $handle = fopen($path, 'r');
        throw_if($handle === false, RuntimeException::class, 'Could not open TSV file: ' . $path);

        fgetcsv($handle, 0, "\t");

        $map = [];
        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            $code = mb_trim($row[0] ?? '');
            if ($code === '') {
                continue;
            }

            $images = array_values(array_filter([
                mb_trim($row[2] ?? ''),
                mb_trim($row[3] ?? ''),
                mb_trim($row[4] ?? ''),
            ], fn (string $url): bool => $url !== ''));
            if ($images === []) {
                continue;
            }

            if (isset($map[$code])) {
                continue;
            }

            $map[$code] = $images;
        }

        fclose($handle);

        return $map;
    }

    /**
     * Converts a group code to a SQL LIKE pattern: the literal "..." becomes a
     * wildcard, and a bare code is treated as a prefix. Literal LIKE
     * metacharacters in the code are escaped.
     */
    private function toLikePattern(string $code): string
    {
        $parts = preg_split('/\s*\.\.\.\s*/', $code) ?: [$code];
        $like = implode('%', array_map(fn (string $part): string => addcslashes($part, '%_\\'), $parts));

        if (! str_contains($code, '...')) {
            $like .= '%';
        }

        return $like;
    }
}
