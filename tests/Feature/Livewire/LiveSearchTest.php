<?php

declare(strict_types=1);

use App\Livewire\LiveSearch;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('renders successfully', function (): void {
    Livewire::test(LiveSearch::class)
        ->assertStatus(200);
});

it('does not search with less than 2 characters', function (): void {
    Livewire::test(LiveSearch::class)
        ->set('query', 'a')
        ->assertSet('showResults', false);
});

it('shows results when query has 2 or more characters', function (): void {
    Product::factory()->create([
        'name' => 'Test Product',
        'product_code' => 'TEST123',
    ]);

    Livewire::test(LiveSearch::class)
        ->set('query', 'TEST')
        ->assertSet('showResults', true);
});

it('finds products by product code', function (): void {
    $product = Product::factory()->create([
        'name' => 'Some Product',
        'product_code' => 'ABC123',
    ]);

    $component = Livewire::test(LiveSearch::class)
        ->set('query', 'ABC');

    expect($component->get('results'))->toHaveCount(1);
    expect($component->get('results')->first()->product_code)->toBe('ABC123');
});

it('finds products by name', function (): void {
    $product = Product::factory()->create([
        'name' => 'Golyóscsapágy 6205',
        'product_code' => 'XYZ999',
    ]);

    $component = Livewire::test(LiveSearch::class)
        ->set('query', 'Golyós');

    expect($component->get('results'))->toHaveCount(1);
    expect($component->get('results')->first()->name)->toBe('Golyóscsapágy 6205');
});

it('treats a decimal comma and a decimal point as the same', function (string $query): void {
    Product::factory()->create([
        'name' => 'SKF hüvelyes csapágy',
        'product_code' => 'R 16-2Z_S',
        'size' => '25,4X50,8X6,35',
    ]);
    Product::factory()->create([
        'name' => 'Másik csapágy',
        'product_code' => 'R 20_S',
        'size' => '31,75X50,8X9,525',
    ]);

    $component = Livewire::test(LiveSearch::class)->set('query', $query);

    expect($component->get('results'))->toHaveCount(1)
        ->and($component->get('results')->first()->product_code)->toBe('R 16-2Z_S');
})->with([
    'decimal point' => '25.4x50.8x6.35',
    'decimal comma' => '25,4x50,8x6,35',
]);

it('shows the stored featured image of a result instead of the raw path', function (): void {
    Product::factory()->create([
        'name' => 'Képes termék',
        'product_code' => 'KEP-001',
        'featured_image' => 'products/abc.jpg',
        'images' => null,
    ]);

    Livewire::test(LiveSearch::class)
        ->set('query', 'KEP-001')
        ->assertSeeHtml('src="' . Storage::disk('public')->url('products/abc.jpg') . '"');
});
