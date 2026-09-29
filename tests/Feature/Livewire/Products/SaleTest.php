<?php

declare(strict_types=1);

use App\Livewire\Products\Sale;
use App\Models\Product;
use Livewire\Livewire;
use Tests\TestCase;

it('lists only the web-visible sale products: in stock with a photo first, then biggest discount', function (): void {
    /** @var TestCase $this */
    $small = Product::factory()->create(['name' => 'Kis akció', 'is_on_sale' => true, 'sale_percentage' => 10, 'stock_quantity' => 3, 'featured_image' => 'products/kis.jpg']);
    $big = Product::factory()->create(['name' => 'Nagy akció', 'is_on_sale' => true, 'sale_percentage' => 70, 'stock_quantity' => 3, 'featured_image' => 'products/nagy.jpg']);
    $noPhoto = Product::factory()->create(['name' => 'Kép nélkül', 'is_on_sale' => true, 'sale_percentage' => 90, 'stock_quantity' => 3, 'featured_image' => null, 'images' => null]);
    $outOfStock = Product::factory()->create(['name' => 'Nincs készleten', 'is_on_sale' => true, 'sale_percentage' => 95, 'stock_quantity' => 0, 'featured_image' => 'products/nincs.jpg']);
    Product::factory()->create(['name' => 'Nem akciós', 'is_on_sale' => false]);
    Product::factory()->create(['name' => 'Rejtett akciós', 'is_on_sale' => true, 'sale_percentage' => 50, 'is_web_visible' => false]);

    $component = Livewire::test(Sale::class)->assertOk()->assertSee('Akciók');

    expect($component->instance()->products->pluck('id')->all())->toBe([$big->id, $small->id, $noPhoto->id, $outOfStock->id]);

    $this->get(route('sale'))->assertOk()
        ->assertSeeInOrder(['<h1', 'Akciók'], false)
        ->assertSee('4 termék található');
});

it('keeps the filter sidebar on the sale page', function (): void {
    Product::factory()->create(['is_on_sale' => true, 'stock_quantity' => 5]);
    Product::factory()->create(['is_on_sale' => true, 'stock_quantity' => 0]);

    $component = Livewire::test(Sale::class)->set('selectedFilters.stock', ['in_stock']);

    expect($component->instance()->products)->toHaveCount(1);
});

it('links the navbar Akciók item and the webshop card to the sale page', function (): void {
    /** @var TestCase $this */
    $this->get('/')->assertOk()
        ->assertSeeHtml('<a href="' . route('sale') . '" class="text-gray-700 hover:text-blue-600">Akciók</a>')
        ->assertSeeHtml('<a href="' . route('sale') . '" class="group block">')
        ->assertSee('Webáruház – jelentős kedvezmények')
        ->assertDontSee('20% kedvezmény')
        ->assertDontSeeHtml('webshop.gordulo-simmering.hu');
});
