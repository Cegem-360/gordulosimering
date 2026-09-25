<?php

declare(strict_types=1);

use App\Livewire\CartIcon;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Livewire;

it('renders successfully', function (): void {
    Livewire::test(CartIcon::class)
        ->assertStatus(200);
});

it('shows zero when cart is empty', function (): void {
    Livewire::test(CartIcon::class)
        ->assertSet('itemCount', 0)
        ->assertSet('total', 0);
});

it('shows the cart total at sale prices', function (): void {
    $product = Product::factory()->onSale(30)->create(['net_selling_price' => 1000]);
    resolve(CartService::class)->addItem($product->id, 2);

    Livewire::test(CartIcon::class)->assertSet('total', 1400.0);
});
