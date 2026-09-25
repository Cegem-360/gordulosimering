<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Support\Facades\Http;

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

it('imports straight from the client\'s published sheet with --sheet', function (): void {
    config(['shop.product_images_sheet_url' => 'https://docs.test/kepek.tsv']);
    Http::fake(['docs.test/*' => Http::response("TERMOKKOD\tTERMEKNEV\tKEP 1\tKEP 2\r\nLISTAN/1\tListán\thttps://cdn.test/uj.jpg\t\r\n")]);
    $product = Product::factory()->create(['product_code' => 'LISTAN/1', 'featured_image' => null]);

    $this->artisan('app:import-product-images', ['--sheet' => true, '--fresh' => true])->assertSuccessful();

    expect($product->fresh()->featured_image)->toBe('https://cdn.test/uj.jpg');
});

it('touches no image when the sheet cannot be downloaded', function (int $status, string $body): void {
    config(['shop.product_images_sheet_url' => 'https://docs.test/kepek.tsv']);
    Http::fake(['docs.test/*' => Http::response($body, $status)]);
    $product = Product::factory()->create(['product_code' => 'LISTAN/1', 'featured_image' => 'https://cdn.test/regi.jpg']);

    $this->artisan('app:import-product-images', ['--sheet' => true, '--fresh' => true])->assertFailed();

    expect($product->fresh()->featured_image)->toBe('https://cdn.test/regi.jpg');
})->with([
    'server error' => [500, ''],
    'login page instead of the sheet' => [200, '<!DOCTYPE html><html>Bejelentkezés</html>'],
    'header only' => [200, "TERMOKKOD\tTERMEKNEV\tKEP 1\tKEP 2\r\n"],
]);
