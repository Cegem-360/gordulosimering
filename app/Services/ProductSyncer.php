<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Az ERP webshop-exportjából (40 oszlopos TSV) frissíti a termékeket termékkód
 * alapján. Kizárólag az exportból származó mezőket írja; a webshopban keletkezett
 * adat (slug, képek, dokumentumok, egyedi mezők, kategória-kapcsolat) érintetlen.
 */
final class ProductSyncer
{
    /**
     * Az export oszlopindexeinek leképezése termékmezőkre.
     *
     * @var array<int, string>
     */
    private const array COLUMN_MAP = [
        0 => 'group_code',
        1 => 'is_web_visible',
        2 => 'product_code',
        3 => 'is_service',
        4 => 'name',
        5 => 'catalog_number',
        6 => 'type',
        7 => 'size',
        8 => 'weight',
        9 => 'rating',
        10 => 'quality',
        11 => 'product_variety',
        12 => 'trade_type',
        13 => 'usage_type',
        14 => 'currency_settlement',
        15 => 'discount_group',
        16 => 'is_on_sale',
        17 => 'sale_percentage',
        18 => 'pricing',
        19 => 'net_selling_price',
        20 => 'vat_class',
        21 => 'gross_selling_price',
        22 => 'quantity_unit',
        23 => 'secondary_unit',
        24 => 'minimum_stock',
        25 => 'maximum_stock',
        26 => 'buffer_stock',
        27 => 'order_unit',
        28 => 'ksh_prefix',
        29 => 'ksh_number',
        30 => 'supplier',
        31 => 'short_note',
        32 => 'description',
        33 => 'barcode',
        34 => 'ean_code',
        35 => 'min_order_quantity',
        36 => 'trade_quantity',
        37 => 'pallet_quantity',
        39 => 'is_inactive',
    ];

    /** @var array<int, string> */
    private const array BOOLEAN_FIELDS = ['is_service', 'is_web_visible', 'is_inactive', 'is_on_sale'];

    /** @var array<int, string> */
    private const array DECIMAL_FIELDS = ['weight', 'sale_percentage', 'net_selling_price', 'gross_selling_price'];

    /** @var array<int, string> */
    private const array INTEGER_FIELDS = [
        'minimum_stock', 'maximum_stock', 'buffer_stock', 'order_unit',
        'min_order_quantity', 'trade_quantity', 'pallet_quantity',
    ];

    private const int BATCH_SIZE = 500;

    /**
     * @return array{created: int, updated: int, unchanged: int, deactivated: int, skipped: int}
     */
    public function sync(string $path, bool $onlyWebVisible = false, bool $dryRun = false): array
    {
        $handle = fopen($path, 'r');
        throw_if($handle === false, RuntimeException::class, 'Could not open TSV file: ' . $path);

        fgetcsv($handle, 0, "\t");

        $stats = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'deactivated' => 0, 'skipped' => 0];

        /** @var array<string, true> $seen */
        $seen = [];
        $batch = [];

        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            $data = $this->parseRow($row);
            $code = $data['product_code'];

            $isVisible = $data['is_web_visible'] === true;

            if ($code === null || $data['name'] === null || isset($seen[$code]) || ($onlyWebVisible && ! $isVisible)) {
                $stats['skipped']++;

                continue;
            }

            $seen[$code] = true;
            $batch[$code] = $data;

            if (count($batch) >= self::BATCH_SIZE) {
                $this->flush($batch, $stats, $dryRun);
                $batch = [];
            }
        }

        fclose($handle);

        if ($batch !== []) {
            $this->flush($batch, $stats, $dryRun);
        }

        $stats['deactivated'] = $this->deactivateMissing($seen, $dryRun);

        return $stats;
    }

    /**
     * @param  array<string, array<string, mixed>>  $batch
     * @param  array{created: int, updated: int, unchanged: int, deactivated: int, skipped: int}  $stats
     */
    private function flush(array $batch, array &$stats, bool $dryRun): void
    {
        $existing = Product::query()
            ->whereIn('product_code', array_keys($batch))
            ->get()
            ->keyBy('product_code');

        foreach ($batch as $code => $data) {
            $product = $existing->get($code);

            if (! $product instanceof Product) {
                if (! $dryRun) {
                    $data['slug'] = $this->uniqueSlug($code, $data['name']);
                    Product::query()->create($data);
                }

                $stats['created']++;

                continue;
            }

            $product->fill($data);

            if (! $product->isDirty()) {
                $stats['unchanged']++;

                continue;
            }

            if (! $dryRun) {
                $product->save();
            }

            $stats['updated']++;
        }
    }

    /**
     * Az exportból hiányzó termékek nem törlődnek: elrejtjük és inaktívra állítjuk,
     * hogy a rendelés-előzmény és a termékoldalak ne sérüljenek.
     *
     * @param  array<string, true>  $seen
     */
    private function deactivateMissing(array $seen, bool $dryRun): int
    {
        $toDeactivate = [];

        Product::query()
            ->whereNotNull('product_code')
            ->where(fn ($query) => $query->where('is_web_visible', true)->orWhere('is_inactive', false))
            ->select(['id', 'product_code'])
            ->chunkById(self::BATCH_SIZE, function ($products) use ($seen, &$toDeactivate): void {
                foreach ($products as $product) {
                    if (! isset($seen[(string) $product->product_code])) {
                        $toDeactivate[] = $product->id;
                    }
                }
            });

        if (! $dryRun) {
            foreach (array_chunk($toDeactivate, self::BATCH_SIZE) as $ids) {
                Product::query()->whereIn('id', $ids)->update([
                    'is_web_visible' => false,
                    'is_inactive' => true,
                ]);
            }
        }

        return count($toDeactivate);
    }

    private function uniqueSlug(string $code, string $name): string
    {
        $base = Str::slug($code);

        if ($base === '') {
            $base = Str::slug($name);
        }

        if ($base === '') {
            $base = 'product';
        }

        $slug = $base;
        $suffix = 0;

        while (Product::query()->where('slug', $slug)->exists()) {
            $suffix++;
            $slug = $base . '-' . $suffix;
        }

        return $slug;
    }

    /**
     * @param  array<int, string|null>  $row
     * @return array<string, mixed>
     */
    private function parseRow(array $row): array
    {
        $data = [];

        foreach (self::COLUMN_MAP as $index => $field) {
            $value = mb_trim((string) ($row[$index] ?? ''));

            if ($value === '') {
                $data[$field] = null;

                continue;
            }

            $data[$field] = match (true) {
                in_array($field, self::BOOLEAN_FIELDS, true) => mb_strtolower($value) === 'igen',
                in_array($field, self::DECIMAL_FIELDS, true) => $this->parseDecimal($value),
                in_array($field, self::INTEGER_FIELDS, true) => $this->parseInteger($value),
                default => $value,
            };
        }

        return $data;
    }

    private function parseDecimal(string $value): ?float
    {
        $value = preg_replace('/[^\d.\-]/', '', str_replace(',', '.', $value));

        return $value === '' || $value === null ? null : (float) $value;
    }

    private function parseInteger(string $value): ?int
    {
        $value = preg_replace('/[^\d\-]/', '', $value);

        return $value === '' || $value === null ? null : (int) $value;
    }
}
