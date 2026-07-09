<?php

declare(strict_types=1);

use App\Models\Product;
use Database\Seeders\ProductSeeder;

/**
 * Builds a fixture TSV in the new 40-column webshop export layout.
 *
 * @param  array<int, array<int, string>>  $rows
 */
function writeProductFixture(array $rows): string
{
    $header = array_fill(0, 40, 'col');
    $lines = [implode("\t", $header)];

    foreach ($rows as $row) {
        $cells = array_pad($row, 40, '');
        $lines[] = implode("\t", $cells);
    }

    $path = tempnam(sys_get_temp_dir(), 'termekek_') . '.tsv';
    file_put_contents($path, implode("\n", $lines) . "\n");

    return $path;
}

/**
 * @return array<int, string>
 */
function productRow(array $overrides = []): array
{
    $row = array_fill(0, 40, '');
    $row[2] = 'CODE-1';   // product_code
    $row[4] = 'Termék';   // name

    foreach ($overrides as $index => $value) {
        $row[$index] = $value;
    }

    return $row;
}

afterEach(function (): void {
    if (ProductSeeder::$dataFile !== null && file_exists(ProductSeeder::$dataFile)) {
        unlink(ProductSeeder::$dataFile);
    }
    ProductSeeder::$dataFile = null;
});

it('maps the new 40-column layout onto product fields', function (): void {
    ProductSeeder::$dataFile = writeProductFixture([
        productRow([
            0 => 'FT',
            1 => 'IGEN',
            2 => 'BEHAJTO-1',
            3 => 'Nem',
            4 => 'Mélységhatárolós behajtó hegy PH2',
            8 => '1,5',
            19 => '372,06',
            20 => 'AFA27',
            21 => '472,52',
            24 => '5',
            39 => 'Nem',
        ]),
    ]);

    $this->seed(ProductSeeder::class);

    $product = Product::query()->where('product_code', 'BEHAJTO-1')->firstOrFail();

    expect($product->group_code)->toBe('FT')
        ->and($product->is_web_visible)->toBeTrue()
        ->and($product->is_service)->toBeFalse()
        ->and($product->is_inactive)->toBeFalse()
        ->and($product->name)->toBe('Mélységhatárolós behajtó hegy PH2')
        ->and((float) $product->weight)->toBe(1.5)
        ->and((float) $product->net_selling_price)->toBe(372.06)
        ->and($product->vat_class)->toBe('AFA27')
        ->and((float) $product->gross_selling_price)->toBe(472.52)
        ->and($product->minimum_stock)->toBe(5);
});

it('parses the webshop and inactive flags (IGEN/NEM/empty)', function (): void {
    ProductSeeder::$dataFile = writeProductFixture([
        productRow([1 => 'IGEN', 2 => 'A', 4 => 'A', 39 => 'Nem']),
        productRow([1 => 'NEM', 2 => 'B', 4 => 'B', 39 => 'Igen']),
        productRow([1 => '', 2 => 'C', 4 => 'C', 39 => '']),
    ]);

    $this->seed(ProductSeeder::class);

    $a = Product::query()->where('product_code', 'A')->firstOrFail();
    $b = Product::query()->where('product_code', 'B')->firstOrFail();
    $c = Product::query()->where('product_code', 'C')->firstOrFail();

    expect($a->is_web_visible)->toBeTrue()
        ->and($a->is_inactive)->toBeFalse()
        ->and($b->is_web_visible)->toBeFalse()
        ->and($b->is_inactive)->toBeTrue()
        ->and($c->is_web_visible)->toBeNull()
        ->and($c->is_inactive)->toBeNull();
});

it('skips rows without a name', function (): void {
    ProductSeeder::$dataFile = writeProductFixture([
        productRow([2 => 'HAS-NAME', 4 => 'Van neve']),
        productRow([2 => 'NO-NAME', 4 => '']),
    ]);

    $this->seed(ProductSeeder::class);

    expect(Product::query()->count())->toBe(1)
        ->and(Product::query()->where('product_code', 'HAS-NAME')->exists())->toBeTrue();
});
