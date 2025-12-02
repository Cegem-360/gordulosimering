<?php

declare(strict_types=1);

use App\Livewire\ProductCard;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

it('renders product card successfully', function () {
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'net_selling_price' => 1000,
        'minimum_stock' => 5,
    ]);

    Livewire::test(ProductCard::class, ['product' => $product])
        ->assertStatus(200)
        ->assertSee('Test Product')
        ->assertSee('Készleten');
});

it('shows out of stock badge when product has no stock', function () {
    $product = Product::factory()->create([
        'name' => 'Out of Stock Product',
        'minimum_stock' => 0,
    ]);

    Livewire::test(ProductCard::class, ['product' => $product])
        ->assertStatus(200)
        ->assertSee('Rendelésre');
});

it('can add product to cart when in stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'minimum_stock' => 5,
        'min_order_quantity' => 1,
    ]);

    Livewire::actingAs($user)
        ->test(ProductCard::class, ['product' => $product])
        ->call('addToCart')
        ->assertDispatched('cartUpdated');

    $cart = Cart::where('user_id', $user->id)->first();
    expect($cart)->not->toBeNull();
    expect($cart->items)->toHaveCount(1);
    expect($cart->items->first()->product_id)->toBe($product->id);
});

it('adds minimum order quantity to cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'minimum_stock' => 5,
        'min_order_quantity' => 5,
    ]);

    Livewire::actingAs($user)
        ->test(ProductCard::class, ['product' => $product])
        ->call('addToCart')
        ->assertDispatched('cartUpdated');

    $cart = Cart::where('user_id', $user->id)->first();
    expect($cart->items->first()->quantity)->toBe(5);
});
