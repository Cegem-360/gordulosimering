<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    Storage::fake('public');
});

it('downloads the remote product images and rewrites them to local paths', function (): void {
    Http::fake(['kepek.hu/*' => Http::response('PNG-BYTES', 200, ['Content-Type' => 'image/png'])]);

    $product = Product::factory()->create([
        'featured_image' => 'https://kepek.hu/kep.png',
        'images' => null,
    ]);

    artisan('app:localize-product-images')->assertSuccessful();

    expect($product->refresh()->featured_image)->toStartWith('products/');
});

it('writes nothing when --dry-run is passed', function (): void {
    Http::fake(['kepek.hu/*' => Http::response('PNG-BYTES', 200, ['Content-Type' => 'image/png'])]);

    $product = Product::factory()->create([
        'featured_image' => 'https://kepek.hu/kep.png',
        'images' => null,
    ]);

    artisan('app:localize-product-images', ['--dry-run' => true])->assertSuccessful();

    expect($product->refresh()->featured_image)->toBe('https://kepek.hu/kep.png');
    Http::assertNothingSent();
});
