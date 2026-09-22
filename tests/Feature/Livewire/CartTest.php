<?php

declare(strict_types=1);

use App\Livewire\Cart;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Livewire;

it('renders the cart with an empty basket', function (): void {
    Livewire::test(Cart::class)->assertSuccessful();
});

it('sums the cart totals from the line items', function (): void {
    $bearing = Product::factory()->create(['net_selling_price' => 1000]);
    $seal = Product::factory()->create(['net_selling_price' => 250]);

    $cartService = resolve(CartService::class);
    $cartService->addItem($bearing->id, 3);
    $cartService->addItem($seal->id, 2);

    Livewire::test(Cart::class)
        ->assertSuccessful()
        ->assertSee('5 termék')
        ->assertSee('3 500 Ft')  // subtotal: 3 x 1000 + 2 x 250
        ->assertSee('945 Ft')    // 27% VAT on the subtotal
        ->assertSee('4 445 Ft'); // total
});

/*
 * Asserts the computed totals recompute against the smaller item set. It does not
 * pin removeItem()'s own refresh: Livewire rehydrates $cartItems from the database
 * between requests, so the deleted row drops out either way.
 */
it('recomputes the totals after a line item is removed', function (): void {
    $bearing = Product::factory()->create(['net_selling_price' => 1000]);
    $seal = Product::factory()->create(['net_selling_price' => 250]);

    $cartService = resolve(CartService::class);
    $cartService->addItem($bearing->id, 1);
    $cartService->addItem($seal->id, 1);

    Livewire::test(Cart::class)
        ->call('removeItem', $seal->id)
        ->assertSee('1 termék')
        ->assertSee('1 000 Ft')
        ->assertDontSee('1 250 Ft');
});
