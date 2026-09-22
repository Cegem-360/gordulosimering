<?php

declare(strict_types=1);

use App\Models\Product;
use App\Services\ProductImageLocalizer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('downloads a distinct url once and points every product at the stored file', function (): void {
    Http::fake(['kepek.hu/*' => Http::response('PNG-BYTES', 200, ['Content-Type' => 'image/png'])]);

    $first = Product::factory()->create(['featured_image' => 'https://kepek.hu/kozos.png', 'images' => null]);
    $second = Product::factory()->create(['featured_image' => 'https://kepek.hu/kozos.png', 'images' => null]);

    $stats = resolve(ProductImageLocalizer::class)->localize();

    $path = $first->refresh()->featured_image;

    expect($path)->toStartWith('products/')
        ->and($path)->toEndWith('.png')
        ->and($second->refresh()->featured_image)->toBe($path)
        ->and($stats['urls'])->toBe(1)
        ->and($stats['downloaded'])->toBe(1)
        ->and($stats['products'])->toBe(2);

    Http::assertSentCount(1);
});

it('stores the downloaded file on the public disk', function (): void {
    Http::fake(['kepek.hu/*' => Http::response('PNG-BYTES', 200, ['Content-Type' => 'image/png'])]);

    $product = Product::factory()->create(['featured_image' => 'https://kepek.hu/kep.png', 'images' => null]);

    resolve(ProductImageLocalizer::class)->localize();

    Storage::disk('public')->assertExists($product->refresh()->featured_image);

    expect(Storage::disk('public')->get($product->featured_image))->toBe('PNG-BYTES');
});

it('leaves images that are already stored locally alone', function (): void {
    Http::fake();

    $product = Product::factory()->create(['featured_image' => 'products/mar-itt-van.png', 'images' => null]);

    $stats = resolve(ProductImageLocalizer::class)->localize();

    expect($product->refresh()->featured_image)->toBe('products/mar-itt-van.png')
        ->and($stats['urls'])->toBe(0);

    Http::assertNothingSent();
});

it('rewrites the gallery image urls too', function (): void {
    Http::fake(['kepek.hu/*' => Http::response('PNG-BYTES', 200, ['Content-Type' => 'image/png'])]);

    $product = Product::factory()->create([
        'featured_image' => null,
        'images' => ['https://kepek.hu/galeria-1.png', 'products/sajat.png'],
    ]);

    resolve(ProductImageLocalizer::class)->localize();

    $images = $product->refresh()->images;

    expect($images[0])->toStartWith('products/')
        ->and($images[0])->not->toBe('products/sajat.png')
        ->and($images[1])->toBe('products/sajat.png');
});

it('keeps the original url when the download fails', function (): void {
    Http::fake(['kepek.hu/*' => Http::response('', 404)]);

    $product = Product::factory()->create(['featured_image' => 'https://kepek.hu/nincs.png', 'images' => null]);

    $stats = resolve(ProductImageLocalizer::class)->localize();

    expect($product->refresh()->featured_image)->toBe('https://kepek.hu/nincs.png')
        ->and($stats['failed'])->toBe(1)
        ->and($stats['products'])->toBe(0);
});

it('writes nothing on a dry run', function (): void {
    Http::fake(['kepek.hu/*' => Http::response('PNG-BYTES', 200, ['Content-Type' => 'image/png'])]);

    $product = Product::factory()->create(['featured_image' => 'https://kepek.hu/kep.png', 'images' => null]);

    $stats = resolve(ProductImageLocalizer::class)->localize(dryRun: true);

    expect($product->refresh()->featured_image)->toBe('https://kepek.hu/kep.png')
        ->and($stats['urls'])->toBe(1)
        ->and($stats['products'])->toBe(1);

    Http::assertNothingSent();
    expect(Storage::disk('public')->allFiles())->toBe([]);
});
