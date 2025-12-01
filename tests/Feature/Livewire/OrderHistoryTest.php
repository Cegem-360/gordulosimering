<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Livewire\OrderHistory;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\User;
use Livewire\Livewire;

it('redirects to login when not authenticated', function () {
    $this->get(route('orders.history'))
        ->assertRedirect(route('login'));
});

it('renders successfully when authenticated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(OrderHistory::class)
        ->assertStatus(200);
});

it('displays message when user has no orders', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(OrderHistory::class)
        ->assertSee('Még nincs rendelésed');
});

it('displays user orders', function () {
    $user = User::factory()->create();
    $shippingMethod = ShippingMethod::create([
        'name' => 'Test Shipping',
        'title' => 'Test Shipping Title',
        'slug' => 'test-shipping-1',
        'description' => 'Test description',
        'cost' => 1000,
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'shipping_method_id' => $shippingMethod->id,
        'payment_method' => 'bacs',
        'payment_method_title' => 'Banki átutalás',
        'set_paid' => false,
        'billing_name' => 'Test Customer',
        'billing_address_1' => 'Test Address',
        'billing_city' => 'Budapest',
        'billing_postcode' => '1111',
        'billing_country' => 'Magyarország',
        'billing_email' => 'test@example.com',
        'billing_phone' => '+36301234567',
        'shipping_name' => 'Test Customer',
        'shipping_address_1' => 'Test Address',
        'shipping_city' => 'Budapest',
        'shipping_postcode' => '1111',
        'shipping_country' => 'Magyarország',
        'shipping_tracking_number' => '',
        'order_status' => OrderStatus::PENDING,
        'order_currency' => 'HUF',
        'shipping_cost' => 0,
    ]);

    $this->actingAs($user);

    Livewire::test(OrderHistory::class)
        ->assertSee('#' . $order->id)
        ->assertSee('Függőben');
});

it('does not display other users orders', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $shippingMethod = ShippingMethod::create([
        'name' => 'Test Shipping',
        'title' => 'Test Shipping Title',
        'slug' => 'test-shipping-2',
        'description' => 'Test description',
        'cost' => 1000,
    ]);

    $order = Order::create([
        'user_id' => $user1->id,
        'shipping_method_id' => $shippingMethod->id,
        'payment_method' => 'bacs',
        'payment_method_title' => 'Banki átutalás',
        'set_paid' => false,
        'billing_name' => 'User 1 Order',
        'billing_address_1' => 'Test Address',
        'billing_city' => 'Budapest',
        'billing_postcode' => '1111',
        'billing_country' => 'Magyarország',
        'billing_email' => 'test@example.com',
        'billing_phone' => '+36301234567',
        'shipping_name' => 'User 1 Order',
        'shipping_address_1' => 'Test Address',
        'shipping_city' => 'Budapest',
        'shipping_postcode' => '1111',
        'shipping_country' => 'Magyarország',
        'shipping_tracking_number' => '',
        'order_status' => OrderStatus::PENDING,
        'order_currency' => 'HUF',
        'shipping_cost' => 0,
    ]);

    $this->actingAs($user2);

    Livewire::test(OrderHistory::class)
        ->assertDontSee('#' . $order->id)
        ->assertSee('Még nincs rendelésed');
});

it('shows correct status labels', function () {
    $user = User::factory()->create();
    $shippingMethod = ShippingMethod::create([
        'name' => 'Test Shipping',
        'title' => 'Test Shipping Title',
        'slug' => 'test-shipping-3',
        'description' => 'Test description',
        'cost' => 1000,
    ]);

    Order::create([
        'user_id' => $user->id,
        'shipping_method_id' => $shippingMethod->id,
        'payment_method' => 'bacs',
        'payment_method_title' => 'Banki átutalás',
        'set_paid' => false,
        'billing_name' => 'Test Customer',
        'billing_address_1' => 'Test Address',
        'billing_city' => 'Budapest',
        'billing_postcode' => '1111',
        'billing_country' => 'Magyarország',
        'billing_email' => 'test@example.com',
        'billing_phone' => '+36301234567',
        'shipping_name' => 'Test Customer',
        'shipping_address_1' => 'Test Address',
        'shipping_city' => 'Budapest',
        'shipping_postcode' => '1111',
        'shipping_country' => 'Magyarország',
        'shipping_tracking_number' => '',
        'order_status' => OrderStatus::COMPLETED,
        'order_currency' => 'HUF',
        'shipping_cost' => 0,
    ]);

    $this->actingAs($user);

    Livewire::test(OrderHistory::class)
        ->assertSee('Teljesítve');
});
