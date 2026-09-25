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

it('charges sale products at their sale price and shows the saving', function (): void {
    $onSale = Product::factory()->onSale(30)->create(['net_selling_price' => 1000]);
    $regular = Product::factory()->create(['net_selling_price' => 250]);

    $cartService = resolve(CartService::class);
    $cartService->addItem($onSale->id, 2);
    $cartService->addItem($regular->id, 2);

    $component = Livewire::test(Cart::class);

    expect($component->instance()->subtotal)->toBe(1900.0); // 2 × 700 + 2 × 250

    $component
        ->assertSee('1 900 Ft')
        ->assertSee('Megtakarítás')
        ->assertSee('600 Ft');            // 2 × (1000 − 700)
});

it('hides the saving row when nothing in the cart is on sale', function (): void {
    $regular = Product::factory()->create(['net_selling_price' => 250]);
    resolve(CartService::class)->addItem($regular->id, 1);

    Livewire::test(Cart::class)->assertDontSee('Megtakarítás');
});

it('follows a sale that changes while the product sits in the cart', function (): void {
    $product = Product::factory()->onSale(30)->create(['net_selling_price' => 1000]);
    resolve(CartService::class)->addItem($product->id, 1);

    $product->update(['is_on_sale' => false]);

    expect(Livewire::test(Cart::class)->instance()->subtotal)->toBe(1000.0);
});
