<?php

declare(strict_types=1);

use App\Livewire\ThankYou;
use App\Models\Order;
use App\Models\ShippingMethod;
use Livewire\Livewire;

it('renders successfully without order', function () {
    Livewire::test(ThankYou::class)
        ->assertStatus(200)
        ->assertSee('Nincs megjeleníthető rendelés');
});

it('renders order when session has order id', function () {
    $shippingMethod = ShippingMethod::create([
        'name' => 'Test Shipping',
        'title' => 'Test Shipping Title',
        'slug' => 'test-shipping',
        'description' => 'Test description',
        'cost' => 1500,
    ]);

    $order = Order::create([
        'user_id' => null,
        'shipping_method_id' => $shippingMethod->id,
        'payment_method' => 'bacs',
        'payment_method_title' => 'Banki átutalás',
        'set_paid' => false,
        'billing_name' => 'Test User',
        'billing_address_1' => 'Test Address 1',
        'billing_address_2' => '',
        'billing_city' => 'Budapest',
        'billing_state' => '',
        'billing_postcode' => '1111',
        'billing_country' => 'Magyarország',
        'billing_email' => 'test@example.com',
        'billing_phone' => '+36301234567',
        'billing_vat_number' => '',
        'billing_company_name' => '',
        'billing_company_office' => '',
        'shipping_name' => 'Test User',
        'shipping_address_1' => 'Test Address 1',
        'shipping_address_2' => '',
        'shipping_city' => 'Budapest',
        'shipping_state' => '',
        'shipping_postcode' => '1111',
        'shipping_country' => 'Magyarország',
        'shipping_tracking_number' => '',
        'order_key' => '',
        'order_status' => 'pending',
        'order_currency' => 'HUF',
        'shipping_cost' => 0,
    ]);

    session(['last_order_id' => $order->id]);

    Livewire::test(ThankYou::class)
        ->assertStatus(200)
        ->assertSee('Köszönjük a rendelését!')
        ->assertSee('Rendelésszám: #' . $order->id);
});
