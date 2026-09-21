<?php

declare(strict_types=1);

use App\Models\Product;

use function Pest\Laravel\artisan;

it('fails when the given type image TSV does not exist', function (): void {
    artisan('app:import-type-images', ['--path' => '/nem/letezik.tsv'])
        ->assertFailed();
});

it('assigns type images from the given TSV', function (): void {
    $product = Product::factory()->create([
        'product_variety' => 'szimering',
        'featured_image' => null,
    ]);

    $path = tempnam(sys_get_temp_dir(), 'tipuskep_cmd_') . '.tsv';
    file_put_contents($path, "Termékféleség\tKép URL\nszimering\thttps://kepek.hu/sz.png\n");

    artisan('app:import-type-images', ['--path' => $path])
        ->assertSuccessful();

    expect($product->refresh()->featured_image)->toBe('https://kepek.hu/sz.png');
});

it('writes nothing when --dry-run is passed', function (): void {
    $product = Product::factory()->create([
        'product_variety' => 'szimering',
        'featured_image' => null,
    ]);

    $path = tempnam(sys_get_temp_dir(), 'tipuskep_cmd_') . '.tsv';
    file_put_contents($path, "Termékféleség\tKép URL\nszimering\thttps://kepek.hu/sz.png\n");

    artisan('app:import-type-images', ['--path' => $path, '--dry-run' => true])
        ->assertSuccessful();

    expect($product->refresh()->featured_image)->toBeNull();
});
