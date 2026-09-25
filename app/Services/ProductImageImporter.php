<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use RuntimeException;

final class ProductImageImporter
{
    /**
     * A letöltött (lokalizált) képek fájlneve a tartalom sha256 hash-e; az
     * adminból feltöltött képek alkönyvtárba kerülnek, így sosem egyeznek.
     */
    private const string LOCALIZED_IMAGE_PATTERN = '#^products/[0-9a-f]{64}\.\w+$#';

    /**
     * Assigns images to products from a TSV (TERMÉKKÓD, TERMÉKNÉV, KÉP 1..3).
     *
     * A product code may be exact or a group code using "..." as a wildcard
     * (e.g. "BETA 7352B ..." covers every size variant). Codes that resolve to
     * more than $maxVariantsPerCode products are treated as too generic and
     * skipped, so a single-letter code never paints thousands of products. A
     * bare code only prefixes codes where it ends at a token boundary, so
     * "LOC 270" never covers "LOC 2701" and "KRE" never covers "KRESZ".
     *
     * A fresh import first clears every previously imported image, so codes
     * dropped from the sheet do not leave their image behind.
     *
     * @return array{codes: int, exact: int, wildcard: int, zero: int, skipped: int, products: int, cleared: int, skipped_codes: array<string, int>}
     */
    public function import(string $path, int $maxVariantsPerCode = 25, bool $fresh = false): array
    {
        $map = $this->readImageMap($path);

        $stats = ['codes' => count($map), 'exact' => 0, 'wildcard' => 0, 'zero' => 0, 'skipped' => 0, 'products' => 0];
        $stats['cleared'] = $fresh ? $this->clearImportedImages() : 0;
        $skippedCodes = [];

        foreach ($map as $code => $images) {
            $code = (string) $code;

            $ids = Product::query()->where('product_code', $code)->pluck('id')->all();
            $isExact = $ids !== [];

            if (! $isExact) {
                $ids = Product::query()
                    ->where('product_code', 'LIKE', $this->toLikePattern($code))
                    ->pluck('product_code', 'id')
                    ->filter(fn (string $productCode): bool => str_contains($code, '...') || $this->continuesAtBoundary($code, $productCode))
                    ->keys()
                    ->all();
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
     * Removes the imported images (remote URLs and localized hash-named files)
     * from every product, keeping admin uploads.
     */
    private function clearImportedImages(): int
    {
        $cleared = 0;

        Product::query()
            ->where(fn ($query) => $query->whereNotNull('featured_image')->orWhereNotNull('images'))
            ->select(['id', 'featured_image', 'images'])
            ->chunkById(500, function ($products) use (&$cleared): void {
                foreach ($products as $product) {
                    $featured = is_string($product->featured_image) && $this->isImported($product->featured_image) ? null : $product->featured_image;
                    $images = array_values(array_filter($product->images ?? [], fn (mixed $image): bool => ! is_string($image) || ! $this->isImported($image)));

                    if ($featured === $product->featured_image && $images === ($product->images ?? [])) {
                        continue;
                    }

                    Product::query()->whereKey($product->id)->update([
                        'featured_image' => $featured,
                        'images' => $images === [] ? null : json_encode($images, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    ]);
                    $cleared++;
                }
            });

        return $cleared;
    }

    private function isImported(string $path): bool
    {
        return str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || preg_match(self::LOCALIZED_IMAGE_PATTERN, $path) === 1;
    }

    /**
     * Reads the TSV into a product-code => image-URL list map (first row per
     * code wins). A cell may hold several URLs joined by "||". Rows without
     * any image URL are ignored.
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

            $images = array_values(array_filter(
                array_map(mb_trim(...), explode('||', implode('||', array_slice(array_pad($row, 5, ''), 2, 3)))),
                fn (string $url): bool => $url !== '',
            ));
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

    /**
     * Whether a bare code ends at a token boundary inside the product code:
     * the next character must not carry on the same number or word. A switch
     * between letters and digits ("KRE" → "KRE16") or any separator counts as
     * a boundary.
     */
    private function continuesAtBoundary(string $code, string $productCode): bool
    {
        $last = mb_substr($code, -1);
        $next = mb_substr($productCode, mb_strlen($code), 1);

        if ($next === '') {
            return true;
        }

        if (ctype_digit($last) && ctype_digit($next)) {
            return false;
        }

        return ! (preg_match('/\p{L}/u', $last) && preg_match('/\p{L}/u', $next));
    }
}
