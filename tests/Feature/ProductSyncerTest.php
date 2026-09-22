<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductSyncer;

/**
 * Builds a fixture TSV in the 40-column webshop export layout.
 *
 * @param  array<int, array<int, string>>  $rows
 */
function writeSyncFixture(array $rows): string
{
    $header = array_fill(0, 40, 'col');
    $lines = [implode("\t", $header)];

    foreach ($rows as $row) {
        $lines[] = implode("\t", array_pad($row, 40, ''));
    }

    $path = tempnam(sys_get_temp_dir(), 'sync_') . '.tsv';
    file_put_contents($path, implode("\n", $lines) . "\n");

    return $path;
}

/**
 * @param  array<int, string>  $overrides
 * @return array<int, string>
 */
function syncRow(array $overrides = []): array
{
    $row = array_fill(0, 40, '');
    $row[1] = 'IGEN';
    $row[2] = 'CODE-1';
    $row[4] = 'Termék';
    $row[39] = 'Nem';

    foreach ($overrides as $index => $value) {
        $row[$index] = $value;
    }

    return $row;
}

it('updates the ERP fields of an existing product matched by product code', function (): void {
    $product = Product::query()->create([
        'product_code' => 'BEHAJTO-1',
        'slug' => 'behajto-1',
        'name' => 'Régi név',
        'net_selling_price' => 100,
        'gross_selling_price' => 127,
    ]);

    $path = writeSyncFixture([
        syncRow([
            0 => 'FT',
            2 => 'BEHAJTO-1',
            4 => 'Új név',
            19 => '372,06',
            21 => '472,52',
        ]),
    ]);

    resolve(ProductSyncer::class)->sync($path);

    $product->refresh();

    expect($product->name)->toBe('Új név')
        ->and($product->group_code)->toBe('FT')
        ->and((float) $product->net_selling_price)->toBe(372.06)
        ->and((float) $product->gross_selling_price)->toBe(472.52);
});

it('leaves the slug, images, documents and category links untouched', function (): void {
    $product = Product::query()->create([
        'product_code' => 'BEHAJTO-1',
        'slug' => 'kezzel-adott-slug',
        'name' => 'Régi név',
        'featured_image' => 'uploads/kep.jpg',
        'images' => ['uploads/kep2.jpg'],
        'documents' => ['uploads/adatlap.pdf'],
        'custom_fields' => ['szin' => 'piros'],
    ]);

    $category = Category::query()->create(['name' => 'Csapágy', 'slug' => 'csapagy']);
    $category->products()->attach($product);

    $path = writeSyncFixture([syncRow([2 => 'BEHAJTO-1', 4 => 'Új név'])]);

    resolve(ProductSyncer::class)->sync($path);

    $product->refresh();

    expect($product->name)->toBe('Új név')
        ->and($product->slug)->toBe('kezzel-adott-slug')
        ->and($product->featured_image)->toBe('uploads/kep.jpg')
        ->and($product->images)->toBe(['uploads/kep2.jpg'])
        ->and($product->documents)->toBe(['uploads/adatlap.pdf'])
        ->and($product->custom_fields)->toBe(['szin' => 'piros'])
        ->and($product->categories()->pluck('product_categories.id')->all())->toBe([$category->id]);
});

it('creates products that are missing from the database', function (): void {
    $path = writeSyncFixture([syncRow([2 => 'UJ-KOD', 4 => 'Új termék', 19 => '500'])]);

    $stats = resolve(ProductSyncer::class)->sync($path);

    $product = Product::query()->where('product_code', 'UJ-KOD')->firstOrFail();

    expect($product->name)->toBe('Új termék')
        ->and($product->slug)->toBe('uj-kod')
        ->and((float) $product->net_selling_price)->toBe(500.0)
        ->and($stats['created'])->toBe(1);
});

it('gives a created product a unique slug when the generated one is taken', function (): void {
    Product::query()->create(['product_code' => 'MAS', 'slug' => 'uj-kod', 'name' => 'Más']);

    $path = writeSyncFixture([syncRow([2 => 'UJ-KOD', 4 => 'Új termék'])]);

    resolve(ProductSyncer::class)->sync($path);

    expect(Product::query()->where('product_code', 'UJ-KOD')->value('slug'))->toBe('uj-kod-1');
});

it('deactivates products that are absent from the export', function (): void {
    Product::query()->create([
        'product_code' => 'MEGSZUNT',
        'slug' => 'megszunt',
        'name' => 'Megszűnt termék',
        'is_web_visible' => true,
        'is_inactive' => false,
    ]);

    $path = writeSyncFixture([syncRow([2 => 'MARAD', 4 => 'Marad'])]);

    $stats = resolve(ProductSyncer::class)->sync($path);

    $gone = Product::query()->where('product_code', 'MEGSZUNT')->firstOrFail();

    expect($gone->exists)->toBeTrue()
        ->and($gone->is_web_visible)->toBeFalse()
        ->and($gone->is_inactive)->toBeTrue()
        ->and($stats['deactivated'])->toBe(1);
});

it('does not create products flagged as not web visible when the filter is on', function (): void {
    $path = writeSyncFixture([
        syncRow([1 => 'IGEN', 2 => 'LATHATO', 4 => 'Látható']),
        syncRow([1 => 'NEM', 2 => 'REJTETT', 4 => 'Rejtett']),
    ]);

    $stats = resolve(ProductSyncer::class)->sync($path, onlyWebVisible: true);

    expect(Product::query()->where('product_code', 'LATHATO')->exists())->toBeTrue()
        ->and(Product::query()->where('product_code', 'REJTETT')->exists())->toBeFalse()
        ->and($stats['created'])->toBe(1);
});

it('deactivates a stored product that the export turned not web visible', function (): void {
    Product::query()->create([
        'product_code' => 'ELREJTETT',
        'slug' => 'elrejtett',
        'name' => 'Elrejtett',
        'is_web_visible' => true,
        'is_inactive' => false,
    ]);

    $path = writeSyncFixture([syncRow([1 => 'NEM', 2 => 'ELREJTETT', 4 => 'Elrejtett'])]);

    $stats = resolve(ProductSyncer::class)->sync($path, onlyWebVisible: true);

    $product = Product::query()->where('product_code', 'ELREJTETT')->firstOrFail();

    expect($product->is_web_visible)->toBeFalse()
        ->and($product->is_inactive)->toBeTrue()
        ->and($stats['deactivated'])->toBe(1);
});

it('reports the counts without writing anything on a dry run', function (): void {
    Product::query()->create([
        'product_code' => 'MEGSZUNT',
        'slug' => 'megszunt',
        'name' => 'Megszűnt',
        'is_web_visible' => true,
        'is_inactive' => false,
    ]);
    Product::query()->create([
        'product_code' => 'VALTOZIK',
        'slug' => 'valtozik',
        'name' => 'Régi név',
    ]);

    $path = writeSyncFixture([
        syncRow([2 => 'VALTOZIK', 4 => 'Új név']),
        syncRow([2 => 'UJ', 4 => 'Új termék']),
    ]);

    $stats = resolve(ProductSyncer::class)->sync($path, dryRun: true);

    expect($stats['created'])->toBe(1)
        ->and($stats['updated'])->toBe(1)
        ->and($stats['deactivated'])->toBe(1)
        ->and(Product::query()->where('product_code', 'UJ')->exists())->toBeFalse()
        ->and(Product::query()->where('product_code', 'VALTOZIK')->value('name'))->toBe('Régi név')
        ->and(Product::query()->where('product_code', 'MEGSZUNT')->value('is_inactive'))->toBeFalsy();
});
