<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Livewire\OrderDetail;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\User;
use Livewire\Livewire;

it('redirects to login when not authenticated', function () {
    $shippingMethod = ShippingMethod::create([
        'name' => 'Test Shipping',
        'title' => 'Test Shipping Title',
        'slug' => 'test-shipping-detail-1',
        'description' => 'Test description',
        'cost' => 1000,
    ]);

    $user = User::factory()->create();
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

    $this->get(route('orders.show', $order))
        ->assertRedirect(route('login'));
});

it('renders successfully when authenticated and order belongs to user', function () {
    $user = User::factory()->create();
    $shippingMethod = ShippingMethod::create([
        'name' => 'Test Shipping',
        'title' => 'Test Shipping Title',
        'slug' => 'test-shipping-detail-2',
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

    Livewire::test(OrderDetail::class, ['order' => $order])
        ->assertStatus(200)
        ->assertSee('Rendelés #' . $order->id);
});

it('returns 403 when accessing another users order', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $shippingMethod = ShippingMethod::create([
        'name' => 'Test Shipping',
        'title' => 'Test Shipping Title',
        'slug' => 'test-shipping-detail-3',
        'description' => 'Test description',
        'cost' => 1000,
    ]);

    $order = Order::create([
        'user_id' => $user1->id,
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

    $this->actingAs($user2);

    $this->get(route('orders.show', $order))
        ->assertForbidden();
});

it('displays order details correctly', function () {
    $user = User::factory()->create();
    $shippingMethod = ShippingMethod::create([
        'name' => 'GLS Futárszolgálat',
        'title' => 'GLS',
        'slug' => 'test-shipping-detail-4',
        'description' => 'Test description',
        'cost' => 1500,
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'shipping_method_id' => $shippingMethod->id,
        'payment_method' => 'bacs',
        'payment_method_title' => 'Banki átutalás',
        'set_paid' => false,
        'billing_name' => 'Teszt Vásárló',
        'billing_address_1' => 'Teszt utca 1.',
        'billing_city' => 'Budapest',
        'billing_postcode' => '1111',
        'billing_country' => 'Magyarország',
        'billing_email' => 'test@example.com',
        'billing_phone' => '+36301234567',
        'shipping_name' => 'Teszt Címzett',
        'shipping_address_1' => 'Szállítási utca 2.',
        'shipping_city' => 'Debrecen',
        'shipping_postcode' => '4000',
        'shipping_country' => 'Magyarország',
        'shipping_tracking_number' => 'GLS123456789',
        'order_status' => OrderStatus::PROCESSING,
        'order_currency' => 'HUF',
        'shipping_cost' => 1500,
    ]);

    $this->actingAs($user);

    Livewire::test(OrderDetail::class, ['order' => $order])
        ->assertSee('Rendelés #' . $order->id)
        ->assertSee('Teszt Vásárló')
        ->assertSee('Teszt Címzett')
        ->assertSee('GLS Futárszolgálat')
        ->assertSee('Banki átutalás')
        ->assertSee('GLS123456789')
        ->assertSee('Feldolgozás alatt');
});
