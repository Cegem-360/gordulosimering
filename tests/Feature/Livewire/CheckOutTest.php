<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Livewire\CheckOut;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(CheckOut::class)
        ->assertStatus(200);
});

it('saves cart items as order items when order is created', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create([
        'user_id' => $user->id,
        'session_id' => session()->getId(),
    ]);

    $products = Product::factory()->count(2)->create();

    $cartItem1 = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $products[0]->id,
        'quantity' => 2,
    ]);

    $cartItem2 = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $products[1]->id,
        'quantity' => 3,
    ]);

    $shippingMethod = ShippingMethod::factory()->create();

    Livewire::actingAs($user)
        ->test(CheckOut::class)
        ->set('data.billing_name', 'Test User')
        ->set('data.billing_email', 'test@example.com')
        ->set('data.billing_phone', '+36301234567')
        ->set('data.billing_postcode', '1234')
        ->set('data.billing_city', 'Budapest')
        ->set('data.billing_address_1', 'Test Street 1')
        ->set('data.billing_country', 'Magyarország')
        ->set('selectedShippingMethod', $shippingMethod->id)
        ->set('selectedPaymentMethod', 'bacs')
        ->set('acceptTerms', true)
        ->call('create');

    $order = Order::where('user_id', $user->id)->first();
    expect($order)->not->toBeNull();
    expect($order->order_status)->toBe(OrderStatus::PENDING);

    $orderItems = $order->orderItems;
    expect($orderItems)->toHaveCount(2);

    $firstOrderItem = $orderItems->firstWhere('product_id', $products[0]->id);
    expect($firstOrderItem)->not->toBeNull();
    expect($firstOrderItem->quantity)->toBe(2);
    expect($firstOrderItem->total)->toBe($products[0]->net_selling_price);

    $secondOrderItem = $orderItems->firstWhere('product_id', $products[1]->id);
    expect($secondOrderItem)->not->toBeNull();
    expect($secondOrderItem->quantity)->toBe(3);
    expect($secondOrderItem->total)->toBe($products[1]->net_selling_price);
});

it('clears the cart after successful order', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create([
        'user_id' => $user->id,
        'session_id' => session()->getId(),
    ]);

    $product = Product::factory()->create();
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $shippingMethod = ShippingMethod::factory()->create();

    Livewire::actingAs($user)
        ->test(CheckOut::class)
        ->set('data.billing_name', 'Test User')
        ->set('data.billing_email', 'test@example.com')
        ->set('data.billing_phone', '+36301234567')
        ->set('data.billing_postcode', '1234')
        ->set('data.billing_city', 'Budapest')
        ->set('data.billing_address_1', 'Test Street 1')
        ->set('data.billing_country', 'Magyarország')
        ->set('selectedShippingMethod', $shippingMethod->id)
        ->set('selectedPaymentMethod', 'bacs')
        ->set('acceptTerms', true)
        ->call('create');

    expect(CartItem::where('cart_id', $cart->id)->count())->toBe(0);
});
