<?php

declare(strict_types=1);

use App\Models\Product;

function writeCommandImageFixture(string $row): string
{
    $path = tempnam(sys_get_temp_dir(), 'kep_parancs_') . '.tsv';
    file_put_contents($path, "TERMOKKOD\tTERMEKNEV\tKEP 1\tKEP 2\n{$row}\n");

    return $path;
}

it('fails when the given image TSV does not exist', function (): void {
    $this->artisan('app:import-product-images', ['--path' => '/nincs/ilyen.tsv'])
        ->assertFailed();
});

it('clears images of codes dropped from the sheet with --fresh', function (): void {
    $dropped = Product::factory()->create(['product_code' => 'KIKERULT/1', 'featured_image' => 'https://cdn.test/regi.jpg']);
    $listed = Product::factory()->create(['product_code' => 'LISTAN/1', 'featured_image' => null]);

    $this->artisan('app:import-product-images', [
        '--path' => writeCommandImageFixture("LISTAN/1\tListán\thttps://cdn.test/uj.jpg\t"),
        '--fresh' => true,
    ])->assertSuccessful();

    expect($dropped->fresh()->featured_image)->toBeNull()
        ->and($listed->fresh()->featured_image)->toBe('https://cdn.test/uj.jpg');
});

it('leaves images of dropped codes alone without --fresh', function (): void {
    $dropped = Product::factory()->create(['product_code' => 'KIKERULT/1', 'featured_image' => 'https://cdn.test/regi.jpg']);

    $this->artisan('app:import-product-images', [
        '--path' => writeCommandImageFixture("LISTAN/1\tListán\thttps://cdn.test/uj.jpg\t"),
    ])->assertSuccessful();

    expect($dropped->fresh()->featured_image)->toBe('https://cdn.test/regi.jpg');
});
