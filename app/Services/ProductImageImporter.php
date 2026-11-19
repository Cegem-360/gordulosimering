<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Database\Query\Builder;
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

        $families = [];

        foreach ($map as $code => $row) {
            $code = (string) $code;
            $isSkf = $this->isSkfRow($row['name']);
            $items = $this->splitCodeList($code);

            if ($items === null && (! $isSkf || Product::query()->where('product_code', $code)->exists())) {
                continue;
            }

            $ids = $this->idsForCodeList($items ?? [$code], $isSkf);
            unset($map[$code]);

            if ($ids === []) {
                $stats['zero']++;

                continue;
            }

            if (count($ids) > ($isSkf ? PHP_INT_MAX : $maxVariantsPerCode * count($items ?? [$code]))) {
                $stats['skipped']++;
                $skippedCodes[$code] = count($ids);

                continue;
            }

            $families[] = ['ids' => $ids, 'images' => $row['images']];
        }

        usort($families, fn (array $a, array $b): int => count($b['ids']) <=> count($a['ids']));

        foreach ($families as $family) {
            $stats['wildcard']++;
            $this->assignImages($family['ids'], $family['images']);
            $stats['products'] += count($family['ids']);
        }

        foreach ($map as $code => $row) {
            $code = (string) $code;
            $images = $row['images'];

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

            $this->assignImages($ids, $images);

            $stats['products'] += count($ids);
        }

        arsort($skippedCodes);
        $stats['skipped_codes'] = $skippedCodes;

        return $stats;
    }

    /**
     * @param  array<int, int>  $ids
     * @param  array<int, string>  $images
     */
    private function assignImages(array $ids, array $images): void
    {
        foreach (array_chunk($ids, 1000) as $chunk) {
            Product::query()->whereIn('id', $chunk)->update([
                'featured_image' => $images[0],
                'images' => json_encode(array_slice($images, 1), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    /**
     * Splits a code cell listing several codes ("51100, 51200, BA") into its
     * items, or returns null when the cell is a single code. A "... suffix"
     * on the last item belongs to every item ("22200, 22300 ... K"). Cells
     * where an earlier item already has a wildcard ("TORRO ... W1, W2") are
     * descriptions, not lists, and stay a single code.
     *
     * @return array<int, string>|null
     */
    private function splitCodeList(string $code): ?array
    {
        if (! str_contains($code, ',')) {
            return null;
        }

        $items = array_values(array_filter(array_map(mb_trim(...), explode(',', $code)), fn (string $item): bool => $item !== ''));
        $last = array_pop($items);

        if ($last === null || $items === []) {
            return null;
        }

        foreach ($items as $item) {
            if (str_contains($item, '...')) {
                return null;
            }
        }

        $suffix = '';
        if (preg_match('/^(.+?)\s*(\.\.\..*)$/u', $last, $matches) === 1) {
            [, $last, $suffix] = $matches;
            $suffix = ' ' . $suffix;
        }

        return array_map(fn (string $item): string => $item . $suffix, [...$items, $last]);
    }

    /**
     * The SKF rows of the sheet ("SKF beálló golyóscsapágy") name catalogue
     * families ("NU", "6200", "W 6"), so they skip the per-code cap, but only
     * paint SKF products, whose codes end in "_S"; a bare series like "6200"
     * would otherwise land on every other brand's bearing of that size too.
     */
    private function isSkfRow(string $name): bool
    {
        return str_starts_with(mb_strtoupper($name), 'SKF ');
    }

    /**
     * Products covered by any item of a code list. An item ending in a number
     * of three or more digits ending in "00" is a catalogue series: "6200"
     * covers 6200-6299 (but not 62200), "SY 500" covers SY 5xx. Any other
     * item is a prefix ending at a token boundary, so "NU" never covers "NUP".
     * A family image never replaces a picture uploaded in the admin.
     *
     * @param  array<int, string>  $items
     * @return array<int, int>
     */
    private function idsForCodeList(array $items, bool $skfOnly): array
    {
        $ids = [];

        foreach ($items as $item) {
            $parts = preg_split('/\s*\.\.\.\s*/', $item) ?: [$item];

            if (count($parts) > 1 && end($parts) === '') {
                array_pop($parts);
            }

            $head = array_shift($parts);
            $isSeries = preg_match('/^(.*?)(\d+)00$/u', $head, $matches) === 1 && mb_strlen($matches[2]) >= 1;
            $prefix = $isSeries ? mb_substr($head, 0, -2) : $head;

            $like = implode('%', array_map(fn (string $part): string => addcslashes($part, '%_\\'), [$prefix, ...$parts])) . '%';

            $pattern = '/^' . preg_quote($prefix, '/') . '\d{2}(?!\d)/u';

            $matched = Product::query()->where('product_code', 'LIKE', $like)->get(['id', 'product_code', 'featured_image'])
                ->filter(fn (Product $product): bool => ! is_string($product->featured_image) || $this->isImported($product->featured_image))
                ->filter(fn (Product $product): bool => ! $skfOnly || str_ends_with((string) $product->product_code, '_S'))
                ->filter(fn (Product $product): bool => match (true) {
                    $isSeries => preg_match($pattern, (string) $product->product_code) === 1,
                    str_contains($item, '...') => true,
                    default => $this->continuesAtBoundary($head, (string) $product->product_code),
                })
                ->pluck('id')
                ->all();

            $ids = [...$ids, ...$matched];
        }

        return array_values(array_unique($ids));
    }

    /**
     * Removes the imported images (remote URLs and localized hash-named files)
     * from every product, keeping admin uploads.
     */
    private function clearImportedImages(): int
    {
        $cleared = 0;

        Product::query()
            ->where(fn (Builder $query) => $query->whereNotNull('featured_image')->orWhereNotNull('images'))
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
     * Reads the TSV into a product-code => name and image-URL list map (first
     * row per code wins). A cell may hold several URLs joined by "||". Rows without
     * any image URL are ignored.
     *
     * @return array<string, array{name: string, images: array<int, string>}>
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

            $map[$code] = ['name' => mb_trim($row[1] ?? ''), 'images' => $images];
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

        return ! preg_match('/\p{L}/u', $last) || ! preg_match('/\p{L}/u', $next);
    }
}
