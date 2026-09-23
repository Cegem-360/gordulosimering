<?php

declare(strict_types=1);

use App\Livewire\Products\Index;
use App\Models\Product;
use Livewire\Livewire;

it('renders successfully', function (): void {
    Livewire::test(Index::class)
        ->assertStatus(200);
});

it('does not filter products with search less than 2 characters', function (): void {
    Product::factory()->create(['name' => 'Test Product']);
    Product::factory()->create(['name' => 'Another Product']);

    $component = Livewire::test(Index::class)
        ->set('search', 'a');

    expect($component->get('products')->total())->toBe(2);
});

it('filters products by product code', function (): void {
    Product::factory()->create([
        'name' => 'Ball Bearing',
        'product_code' => 'SKF6205',
    ]);
    Product::factory()->create([
        'name' => 'Other Bearing',
        'product_code' => 'NTN3210',
    ]);

    $component = Livewire::test(Index::class)
        ->set('search', 'SKF');

    expect($component->get('products')->total())->toBe(1);
    expect($component->get('products')->first()->product_code)->toBe('SKF6205');
});

it('filters products by name', function (): void {
    Product::factory()->create([
        'name' => 'Golyóscsapágy 6205',
        'product_code' => 'ABC123',
    ]);
    Product::factory()->create([
        'name' => 'Tűgörgő',
        'product_code' => 'XYZ789',
    ]);

    $component = Livewire::test(Index::class)
        ->set('search', 'Golyós');

    expect($component->get('products')->total())->toBe(1);
    expect($component->get('products')->first()->name)->toBe('Golyóscsapágy 6205');
});

it('orders search results by relevance with prefix matches first', function (): void {
    Product::factory()->create([
        'name' => 'Product contains SKF in name',
        'product_code' => 'OTHER001',
    ]);
    Product::factory()->create([
        'name' => 'Another Product',
        'product_code' => 'SKF6205',
    ]);

    $component = Livewire::test(Index::class)
        ->set('search', 'SKF');

    $products = $component->get('products');
    expect($products->first()->product_code)->toBe('SKF6205');
});

it('clears search when clearFilters is called', function (): void {
    Product::factory()->create(['name' => 'Test Product']);

    $component = Livewire::test(Index::class)
        ->set('search', 'Test')
        ->call('clearFilters')
        ->assertSet('search', '');
});

it('resets pagination when search changes', function (): void {
    Product::factory()->count(30)->create(['name' => 'Test Product']);

    Livewire::withQueryParams(['page' => 2])
        ->test(Index::class)
        ->set('search', 'Test')
        ->assertNotDispatched('gotoPage');
});

it('accepts search from URL query parameter', function (): void {
    Product::factory()->create([
        'name' => 'SKF Bearing',
        'product_code' => 'SKF6205',
    ]);

    $component = Livewire::withQueryParams(['search' => 'SKF'])
        ->test(Index::class);

    expect($component->get('search'))->toBe('SKF');
    expect($component->get('products')->total())->toBe(1);
});

it('finds a size written with a decimal comma when searching with a decimal point', function (): void {
    Product::factory()->create(['name' => 'Hüvelyes csapágy', 'product_code' => 'R 16', 'size' => '25,4X50,8X6,35']);
    Product::factory()->create(['name' => 'Másik csapágy', 'product_code' => 'R 20', 'size' => '31,75X50,8X9,525']);

    $component = Livewire::test(Index::class)->set('search', '25.4x50.8');

    expect($component->get('products')->total())->toBe(1)
        ->and($component->get('products')->first()->product_code)->toBe('R 16');
});

it('lists the stock filter first', function (): void {
    $component = Livewire::test(Index::class);

    expect($component->instance()->filters()[0]['key'])->toBe('stock');
});
