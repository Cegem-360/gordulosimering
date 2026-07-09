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

    /**
     * Fields that should be parsed as booleans (Igen/Nem).
     *
     * @var array<int, string>
     */
    private const array BOOLEAN_FIELDS = ['is_service', 'is_web_visible', 'is_inactive', 'is_on_sale'];

    /**
     * Fields that should be parsed as decimals.
     *
     * @var array<int, string>
     */
    private const array DECIMAL_FIELDS = [
        'weight',
        'sale_percentage',
        'net_selling_price',
        'gross_selling_price',
    ];

    /**
     * Fields that should be parsed as integers.
     *
     * @var array<int, string>
     */
    private const array INTEGER_FIELDS = [
        'minimum_stock',
        'maximum_stock',
        'buffer_stock',
        'order_unit',
        'min_order_quantity',
        'trade_quantity',
        'pallet_quantity',
    ];

    /**
     * Overrides the default TSV path (used by tests to point at a fixture).
     */
    public static ?string $dataFile = null;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = self::$dataFile ?? database_path('data/termekek.tsv');

        if (! file_exists($filePath)) {
            $this->command->error('TSV file not found: ' . $filePath);

            return;
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $this->command->error('Could not open TSV file: ' . $filePath);

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

            Product::query()->create($data);
            $count++;

            if ($count % 1000 === 0) {
                $this->command->info(sprintf('Imported %d products...', $count));
            }
        }

        fclose($handle);
        $this->command->info(sprintf('Successfully imported %d products.', $count));
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
                in_array($field, self::BOOLEAN_FIELDS, true) => $this->parseBoolean($value),
                in_array($field, self::DECIMAL_FIELDS, true) => $this->parseDecimal($value),
                in_array($field, self::INTEGER_FIELDS, true) => $this->parseInteger($value),
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
