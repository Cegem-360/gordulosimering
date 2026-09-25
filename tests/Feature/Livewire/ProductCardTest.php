<?php

declare(strict_types=1);

use App\Livewire\ProductCard;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Number;
use Livewire\Livewire;

it('renders product card successfully', function (): void {
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

it('shows out of stock badge when product has no stock', function (): void {
    $product = Product::factory()->create([
        'name' => 'Out of Stock Product',
        'minimum_stock' => 0,
    ]);

    Livewire::test(ProductCard::class, ['product' => $product])
        ->assertStatus(200)
        ->assertSee('Rendelésre');
});

it('can add product to cart when in stock', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'minimum_stock' => 5,
        'min_order_quantity' => 1,
    ]);

    Livewire::actingAs($user)
        ->test(ProductCard::class, ['product' => $product])
        ->call('addToCart')
        ->assertDispatched('cartUpdated');

    $cart = Cart::query()->where('user_id', $user->id)->first();
    expect($cart)->not->toBeNull();
    expect($cart->items)->toHaveCount(1);
    expect($cart->items->first()->product_id)->toBe($product->id);
});

it('adds minimum order quantity to cart', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'minimum_stock' => 5,
        'min_order_quantity' => 5,
    ]);

    Livewire::actingAs($user)
        ->test(ProductCard::class, ['product' => $product])
        ->call('addToCart')
        ->assertDispatched('cartUpdated');

    $cart = Cart::query()->where('user_id', $user->id)->first();
    expect($cart->items->first()->quantity)->toBe(5);
});

it('shows the product code and the net price with a +ÁFA label, not the product variety', function (): void {
    $product = Product::factory()->create([
        'product_code' => 'TORRO SL 9-11/9 SZW1',
        'product_variety' => 'bilincs',
        'supplier' => null,
        'net_selling_price' => 75,
    ]);

    Livewire::test(ProductCard::class, ['product' => $product])
        ->assertSee('TORRO SL 9-11/9 SZW1')
        ->assertSee('+ÁFA')
        ->assertDontSee('bilincs');
});

it('falls back to the company placeholder instead of a bearing photo', function (): void {
    $product = Product::factory()->create(['featured_image' => null, 'images' => null]);

    Livewire::test(ProductCard::class, ['product' => $product])
        ->assertSeeHtml('product-placeholder')
        ->assertDontSeeHtml('bearing');
});

it('shows the struck-through price, the sale price and the discount on a sale product', function (): void {
    $product = Product::factory()->onSale(30)->create(['net_selling_price' => 1000]);

    Livewire::test(ProductCard::class, ['product' => $product])
        ->assertSeeHtml('line-through')
        ->assertSee(Number::currency(1000, 'HUF', 'hu', 0))
        ->assertSee(Number::currency(700, 'HUF', 'hu', 0))
        ->assertSee('-30%');
});

it('shows a single price on a regular product', function (): void {
    $product = Product::factory()->create(['net_selling_price' => 1000]);

    Livewire::test(ProductCard::class, ['product' => $product])
        ->assertSee(Number::currency(1000, 'HUF', 'hu', 0))
        ->assertDontSeeHtml('line-through');
});
