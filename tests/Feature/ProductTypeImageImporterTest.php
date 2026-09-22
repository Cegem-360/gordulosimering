<?php

declare(strict_types=1);

use App\Models\Product;
use App\Services\ProductTypeImageImporter;

/**
 * Builds a fixture TSV in the type-image layout (Termékféleség, Kép URL).
 *
 * @param  array<int, array{0: string, 1: string}>  $rows
 */
function writeTypeImageFixture(array $rows): string
{
    $lines = ["Termékféleség\tKép URL"];

    foreach ($rows as $row) {
        $lines[] = implode("\t", $row);
    }

    $path = tempnam(sys_get_temp_dir(), 'tipuskep_') . '.tsv';
    file_put_contents($path, implode("\n", $lines) . "\n");

    return $path;
}

it('assigns the type image to products of that variety that have no image', function (): void {
    $product = Product::factory()->create([
        'product_variety' => 'szimering',
        'featured_image' => null,
    ]);

    $path = writeTypeImageFixture([['szimering', 'https://kepek.hu/szimering.png']]);

    $stats = resolve(ProductTypeImageImporter::class)->import($path);

    expect($product->refresh()->featured_image)->toBe('https://kepek.hu/szimering.png')
        ->and($stats['products'])->toBe(1);
});

it('never overwrites a product specific image', function (): void {
    $product = Product::factory()->create([
        'product_variety' => 'szimering',
        'featured_image' => 'uploads/sajat-kep.jpg',
    ]);

    $path = writeTypeImageFixture([['szimering', 'https://kepek.hu/szimering.png']]);

    resolve(ProductTypeImageImporter::class)->import($path);

    expect($product->refresh()->featured_image)->toBe('uploads/sajat-kep.jpg');
});

it('replaces a previously assigned type image when the mapping changes', function (): void {
    $product = Product::factory()->create([
        'product_variety' => 'szimering',
        'featured_image' => 'https://kepek.hu/regi-szimering.png',
    ]);

    $path = writeTypeImageFixture([
        ['szimering', 'https://kepek.hu/uj-szimering.png'],
        ['o gyűrű', 'https://kepek.hu/regi-szimering.png'],
    ]);

    resolve(ProductTypeImageImporter::class)->import($path);

    expect($product->refresh()->featured_image)->toBe('https://kepek.hu/uj-szimering.png');
});

it('leaves the gallery images untouched', function (): void {
    $product = Product::factory()->create([
        'product_variety' => 'szimering',
        'featured_image' => null,
        'images' => ['uploads/galeria-1.jpg'],
    ]);

    $path = writeTypeImageFixture([['szimering', 'https://kepek.hu/szimering.png']]);

    resolve(ProductTypeImageImporter::class)->import($path);

    expect($product->refresh()->images)->toBe(['uploads/galeria-1.jpg']);
});

it('ignores varieties that no product uses', function (): void {
    Product::factory()->create(['product_variety' => 'szimering', 'featured_image' => null]);

    $path = writeTypeImageFixture([
        ['szimering', 'https://kepek.hu/szimering.png'],
        ['nincs ilyen', 'https://kepek.hu/semmi.png'],
    ]);

    $stats = resolve(ProductTypeImageImporter::class)->import($path);

    expect($stats['types'])->toBe(2)
        ->and($stats['matched_types'])->toBe(1)
        ->and($stats['products'])->toBe(1);
});

it('reports the counts without writing anything on a dry run', function (): void {
    $product = Product::factory()->create([
        'product_variety' => 'szimering',
        'featured_image' => null,
    ]);

    $path = writeTypeImageFixture([['szimering', 'https://kepek.hu/szimering.png']]);

    $stats = resolve(ProductTypeImageImporter::class)->import($path, dryRun: true);

    expect($stats['products'])->toBe(1)
        ->and($product->refresh()->featured_image)->toBeNull();
});
