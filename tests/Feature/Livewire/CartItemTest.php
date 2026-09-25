<?php

declare(strict_types=1);

use App\Livewire\CartItem;
use App\Models\Product;
use Illuminate\Support\Number;
use Livewire\Livewire;

it('renders successfully', function (): void {
    $product = Product::factory()->create();

    Livewire::test(CartItem::class, ['productId' => $product->id, 'quantity' => 1])
        ->assertStatus(200);
});

it('prices a sale line item at the sale price', function (): void {
    $product = Product::factory()->onSale(30)->create(['net_selling_price' => 1000]);

    Livewire::test(CartItem::class, ['productId' => $product->id, 'quantity' => 2])
        ->assertSee(Number::currency(700, 'HUF', 'hu', 0))
        ->assertSee(Number::currency(1400, 'HUF', 'hu', 0))
        ->assertSeeHtml('line-through');
});
