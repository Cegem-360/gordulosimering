<?php

declare(strict_types=1);

use App\Livewire\CartItem;
use App\Models\Product;
use Livewire\Livewire;

it('renders successfully', function () {
    $product = Product::factory()->create();

    Livewire::test(CartItem::class, ['productId' => $product->id, 'quantity' => 1])
        ->assertStatus(200);
});
