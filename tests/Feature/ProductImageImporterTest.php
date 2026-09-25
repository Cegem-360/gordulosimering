<?php

declare(strict_types=1);

use App\Models\Product;
use App\Services\ProductImageImporter;

/**
 * @param  array<int, array<int, string>>  $rows  each: [code, name, kep1, kep2, kep3]
 */
function writeImageFixture(array $rows): string
{
    $lines = ["TERMÉKKÓD\tTERMÉKNÉV\tKÉP 1\tKÉP 2\tKÉP 3"];
    foreach ($rows as $row) {
        $lines[] = implode("\t", array_pad($row, 5, ''));
    }

    $path = tempnam(sys_get_temp_dir(), 'kep_') . '.tsv';
    file_put_contents($path, implode("\n", $lines) . "\n");

    return $path;
}

afterEach(function (): void {
    foreach (glob(sys_get_temp_dir() . '/kep_*.tsv') ?: [] as $file) {
        @unlink($file);
    }
});

it('assigns the featured image and gallery on an exact code match', function (): void {
    $product = Product::factory()->create(['product_code' => 'BETA 7399AN/100', 'featured_image' => null, 'images' => null]);

    $path = writeImageFixture([
        ['BETA 7399AN/100', 'BETA cipőfűző', 'https://cdn.test/a.jpg', 'https://cdn.test/b.jpg', ''],
    ]);

    $stats = (new ProductImageImporter())->import($path);

    $product->refresh();
    expect($product->featured_image)->toBe('https://cdn.test/a.jpg')
        ->and($product->images)->toBe(['https://cdn.test/b.jpg'])
        ->and($stats['exact'])->toBe(1)
        ->and($stats['products'])->toBe(1);
});

it('assigns a group image to all size variants via the "..." wildcard', function (): void {
    $v38 = Product::factory()->create(['product_code' => 'BETA 7352B/38']);
    $v39 = Product::factory()->create(['product_code' => 'BETA 7352B/39']);
    $other = Product::factory()->create(['product_code' => 'BETA 9999/10']);

    $path = writeImageFixture([
        ['BETA 7352B ...', 'BETA cipő', 'https://cdn.test/shoe.jpg', '', ''],
    ]);

    $stats = (new ProductImageImporter())->import($path);

    expect($v38->fresh()->featured_image)->toBe('https://cdn.test/shoe.jpg')
        ->and($v39->fresh()->featured_image)->toBe('https://cdn.test/shoe.jpg')
        ->and($other->fresh()->featured_image)->toBeNull()
        ->and($stats['wildcard'])->toBe(1)
        ->and($stats['products'])->toBe(2);
});

it('skips over-generic codes that match more than the max variants', function (): void {
    Product::factory()->count(4)->sequence(
        ['product_code' => 'N100'],
        ['product_code' => 'N200'],
        ['product_code' => 'N300'],
        ['product_code' => 'N400'],
    )->create(['featured_image' => null]);

    $path = writeImageFixture([
        ['N ...', 'Generic', 'https://cdn.test/x.jpg', '', ''],
    ]);

    $stats = (new ProductImageImporter())->import($path, maxVariantsPerCode: 3);

    expect($stats['skipped'])->toBe(1)
        ->and($stats['products'])->toBe(0)
        ->and(Product::query()->whereNotNull('featured_image')->count())->toBe(0)
        ->and($stats['skipped_codes'])->toHaveKey('N ...');
});

it('ignores codes not present in the catalogue and rows without images', function (): void {
    Product::factory()->create(['product_code' => 'EXISTS/1']);

    $path = writeImageFixture([
        ['NOSUCH/1', 'Missing', 'https://cdn.test/x.jpg', '', ''],
        ['EXISTS/1', 'No image row', '', '', ''],
    ]);

    $stats = (new ProductImageImporter())->import($path);

    expect($stats['zero'])->toBe(1)
        ->and($stats['codes'])->toBe(1)
        ->and($stats['products'])->toBe(0);
});

it('does not let a bare code prefix run into a longer number or word', function (): void {
    $bare = Product::factory()->create(['product_code' => 'LOC 270 50ML', 'featured_image' => null]);
    $longerNumber = Product::factory()->create(['product_code' => 'LOC 2701 50ML', 'featured_image' => null]);
    $series = Product::factory()->create(['product_code' => 'KRE16_S', 'featured_image' => null]);
    $longerWord = Product::factory()->create(['product_code' => 'KRESZ TÁBLA', 'featured_image' => null]);

    $path = writeImageFixture([
        ['LOC 270', 'LOCTITE 270', 'https://cdn.test/270.jpg', '', ''],
        ['KRE', 'SKF támasztógörgő', 'https://cdn.test/kre.jpg', '', ''],
    ]);

    $stats = (new ProductImageImporter())->import($path);

    expect($bare->fresh()->featured_image)->toBe('https://cdn.test/270.jpg')
        ->and($longerNumber->fresh()->featured_image)->toBeNull()
        ->and($series->fresh()->featured_image)->toBe('https://cdn.test/kre.jpg')
        ->and($longerWord->fresh()->featured_image)->toBeNull()
        ->and($stats['products'])->toBe(2);
});

it('still lets an explicit "..." wildcard match a longer code', function (): void {
    $product = Product::factory()->create(['product_code' => 'S7014 CEGA/P4A_S', 'featured_image' => null]);

    $path = writeImageFixture([
        ['S7 ...', 'SKF ferde hatásvonalú golyóscsapágy', 'https://cdn.test/s7.jpg', '', ''],
    ]);

    (new ProductImageImporter())->import($path);

    expect($product->fresh()->featured_image)->toBe('https://cdn.test/s7.jpg');
});

it('clears every imported image first on a fresh import, but keeps admin uploads', function (): void {
    $hash = str_repeat('a', 64);
    $stale = Product::factory()->create([
        'product_code' => 'KIKERULT/1',
        'featured_image' => "products/{$hash}.jpg",
        'images' => ["products/{$hash}.png", 'products/images/admin-galeria.jpg'],
    ]);
    $remote = Product::factory()->create(['product_code' => 'KULSO/1', 'featured_image' => 'https://cdn.test/old.jpg', 'images' => null]);
    $uploaded = Product::factory()->create(['product_code' => 'ADMIN/1', 'featured_image' => 'products/featured/admin.jpg', 'images' => null]);
    $listed = Product::factory()->create(['product_code' => 'LISTAN/1', 'featured_image' => "products/{$hash}.jpg", 'images' => null]);

    $path = writeImageFixture([
        ['LISTAN/1', 'Listán', 'https://cdn.test/new.jpg', '', ''],
    ]);

    $stats = (new ProductImageImporter())->import($path, fresh: true);

    expect($stale->fresh()->featured_image)->toBeNull()
        ->and($stale->fresh()->images)->toBe(['products/images/admin-galeria.jpg'])
        ->and($remote->fresh()->featured_image)->toBeNull()
        ->and($uploaded->fresh()->featured_image)->toBe('products/featured/admin.jpg')
        ->and($listed->fresh()->featured_image)->toBe('https://cdn.test/new.jpg')
        ->and($stats['cleared'])->toBe(3);
});

it('keeps existing images when the import is not fresh', function (): void {
    $product = Product::factory()->create([
        'product_code' => 'KIKERULT/1',
        'featured_image' => 'products/' . str_repeat('b', 64) . '.jpg',
    ]);

    (new ProductImageImporter())->import(writeImageFixture([]));

    expect($product->fresh()->featured_image)->toBe('products/' . str_repeat('b', 64) . '.jpg');
});
