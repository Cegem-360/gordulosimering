<?php

declare(strict_types=1);

use App\Livewire\CartItem;
use App\Livewire\ProductCard;
use App\Livewire\Products\Show;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Livewire;

it('works out the orderable quantities from the minimum and the order unit', function (?int $minimum, ?int $unit, int $smallest, array $requested, array $orderable): void {
    $product = Product::factory()->make(['min_order_quantity' => $minimum, 'order_unit' => $unit]);

    expect($product->minimumOrderQuantity())->toBe($smallest)
        ->and(array_map($product->orderableQuantity(...), $requested))->toBe($orderable);
})->with([
    'nothing set' => [null, null, 1, [0, 1, 7], [1, 1, 7]],
    'zeros, as the old export left them' => [0, 0, 1, [3], [3]],
    'minimum only' => [5, 0, 5, [1, 5, 6], [5, 5, 6]],
    'order unit only (batteries in tens)' => [0, 10, 10, [1, 9, 10, 11, 25], [10, 10, 10, 20, 30]],
    'minimum not a multiple of the unit' => [15, 10, 20, [1, 21], [20, 30]],
]);

it('steps the product page quantity in order units and rounds typed amounts up', function (): void {
    $product = Product::factory()->create(['min_order_quantity' => 0, 'order_unit' => 10, 'quantity_unit' => 'db']);

    Livewire::test(Show::class, ['product' => $product])
        ->assertSet('quantity', 10)
        ->call('increment')->assertSet('quantity', 20)
        ->call('decrement')->assertSet('quantity', 10)
        ->call('decrement')->assertSet('quantity', 10)
        ->set('quantity', 9)->assertSet('quantity', 10)
        ->set('quantity', 25)->assertSet('quantity', 30)
        ->assertSee('Rendelési egység: 10 db. Csak ennek többszöröse rendelhető')
        ->assertSeeHtml('step="10"')
        // .live.blur: rounds when the field is left, not on every keystroke.
        // In Livewire 4 a bare .blur never reaches the server.
        ->assertSeeHtml('wire:model.live.blur="quantity"');
});

it('adds the smallest orderable quantity from a product card', function (): void {
    $product = Product::factory()->create(['min_order_quantity' => 0, 'order_unit' => 10]);

    Livewire::test(ProductCard::class, ['product' => $product])->call('addToCart');

    expect(app(CartService::class)->getItem($product->id)?->quantity)->toBe(10);
});

it('keeps the cart quantity to whole order units', function (): void {
    $product = Product::factory()->create(['min_order_quantity' => 0, 'order_unit' => 10, 'maximum_stock' => null]);
    app(CartService::class)->addItem($product->id, 9);

    expect(app(CartService::class)->getItem($product->id)?->quantity)->toBe(10);

    Livewire::test(CartItem::class, ['productId' => $product->id, 'quantity' => 10])
        ->call('increaseQuantity')->assertSet('quantity', 20)
        ->call('decreaseQuantity')->assertSet('quantity', 10)
        ->call('decreaseQuantity')->assertSet('quantity', 10)
        ->set('quantity', 33)->assertSet('quantity', 40);

    expect(app(CartService::class)->getItem($product->id)?->quantity)->toBe(40);
});
