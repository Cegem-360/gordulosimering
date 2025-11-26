<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class ProductSeeder extends Seeder
{
    /**
     * TSV column mapping to database fields.
     *
     * @var array<int, string>
     */
    private const COLUMN_MAP = [
        0 => 'group_code',
        1 => 'product_code',
        2 => 'is_service',
        3 => 'name',
        4 => 'catalog_number',
        5 => 'type',
        6 => 'size',
        7 => 'weight',
        8 => 'rating',
        9 => 'quality',
        10 => 'product_variety',
        11 => 'trade_type',
        12 => 'usage_type',
        13 => 'currency_settlement',
        14 => 'discount_group',
        15 => 'is_on_sale',
        16 => 'sale_percentage',
        17 => 'pricing',
        18 => 'list_price',
        19 => 'list_discount',
        20 => 'purchase_currency_price',
        21 => 'currency',
        22 => 'currency_multiplier',
        23 => 'purchase_price',
        24 => 'profit_margin',
        25 => 'net_selling_price',
        26 => 'vat_class',
        27 => 'gross_selling_price',
        28 => 'quantity_unit',
        29 => 'secondary_unit',
        30 => 'minimum_stock',
        31 => 'maximum_stock',
        32 => 'buffer_stock',
        33 => 'order_unit',
        34 => 'ksh_prefix',
        35 => 'ksh_number',
        36 => 'supplier',
        37 => 'short_note',
        38 => 'description',
        39 => 'barcode',
        40 => 'ean_code',
        41 => 'min_order_quantity',
        42 => 'trade_quantity',
        43 => 'pallet_quantity',
    ];

    /**
     * Fields that should be parsed as booleans (Igen/Nem).
     *
     * @var array<int, string>
     */
    private const BOOLEAN_FIELDS = ['is_service', 'is_on_sale'];

    /**
     * Fields that should be parsed as decimals.
     *
     * @var array<int, string>
     */
    private const DECIMAL_FIELDS = [
        'weight',
        'sale_percentage',
        'list_price',
        'list_discount',
        'purchase_currency_price',
        'currency_multiplier',
        'purchase_price',
        'profit_margin',
        'net_selling_price',
        'gross_selling_price',
    ];

    /**
     * Fields that should be parsed as integers.
     *
     * @var array<int, string>
     */
    private const INTEGER_FIELDS = [
        'minimum_stock',
        'maximum_stock',
        'buffer_stock',
        'order_unit',
        'min_order_quantity',
        'trade_quantity',
        'pallet_quantity',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = database_path('data/termekek.tsv');

        if (! file_exists($filePath)) {
            $this->command->error("TSV file not found: {$filePath}");

            return;
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $this->command->error("Could not open TSV file: {$filePath}");

            return;
        }

        // Skip header row
        fgetcsv($handle, 0, "\t");

        $count = 0;
        $slugCounts = [];

        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            $data = $this->parseRow($row);

            if (empty($data['name'])) {
                continue;
            }

            // Generate unique slug using product_code (which is unique)
            $data['slug'] = $this->generateUniqueSlug($data, $slugCounts);

            Product::create($data);
            $count++;

            if ($count % 1000 === 0) {
                $this->command->info("Imported {$count} products...");
            }
        }

        fclose($handle);
        $this->command->info("Successfully imported {$count} products.");
    }

    /**
     * Parse a TSV row into product data.
     *
     * @param  array<int, string|null>  $row
     * @return array<string, mixed>
     */
    private function parseRow(array $row): array
    {
        $data = [];

        foreach (self::COLUMN_MAP as $index => $field) {
            $value = $row[$index] ?? null;
            $value = $this->cleanValue($value);

            if ($value === null || $value === '') {
                $data[$field] = null;

                continue;
            }

            $data[$field] = match (true) {
                in_array($field, self::BOOLEAN_FIELDS) => $this->parseBoolean($value),
                in_array($field, self::DECIMAL_FIELDS) => $this->parseDecimal($value),
                in_array($field, self::INTEGER_FIELDS) => $this->parseInteger($value),
                default => $value,
            };
        }

        return $data;
    }

    /**
     * Clean a value by trimming whitespace and handling empty strings.
     */
    private function cleanValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = mb_trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * Parse Hungarian boolean values (Igen/Nem).
     */
    private function parseBoolean(string $value): bool
    {
        return mb_strtolower($value) === 'igen';
    }

    /**
     * Parse decimal values with Hungarian format (comma as decimal separator).
     */
    private function parseDecimal(string $value): ?float
    {
        // Replace comma with dot for decimal separator
        $value = str_replace(',', '.', $value);

        // Remove any non-numeric characters except dot and minus
        $value = preg_replace('/[^\d.\-]/', '', $value);

        if ($value === '' || $value === null) {
            return null;
        }

        return (float) $value;
    }

    /**
     * Parse integer values.
     */
    private function parseInteger(string $value): ?int
    {
        // Remove any non-numeric characters except minus
        $value = preg_replace('/[^\d\-]/', '', $value);

        if ($value === '' || $value === null) {
            return null;
        }

        return (int) $value;
    }

    /**
     * Generate a unique slug for the product.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, int>  $slugCounts
     */
    private function generateUniqueSlug(array $data, array &$slugCounts): string
    {
        // Prefer product_code for slug as it's more unique
        $baseSlug = Str::slug($data['product_code'] ?? '');

        // Fallback to name if product_code is empty
        if (empty($baseSlug)) {
            $baseSlug = Str::slug($data['name'] ?? 'product');
        }

        // Ensure we have something
        if (empty($baseSlug)) {
            $baseSlug = 'product';
        }

        // Track and increment for duplicates
        if (isset($slugCounts[$baseSlug])) {
            $slugCounts[$baseSlug]++;

            return $baseSlug . '-' . $slugCounts[$baseSlug];
        }

        $slugCounts[$baseSlug] = 0;

        return $baseSlug;
    }
}
