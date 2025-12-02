<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Livewire\CheckOut;
use App\Mail\NewOrderNotificationMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('renders successfully', function () {
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

    Livewire::actingAs($user)
        ->test(CheckOut::class)
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

it('pre-fills form with saved user billing data', function () {
    $user = User::factory()->create([
        'billing_name' => 'Saved Name',
        'billing_company_name' => 'Saved Company',
        'billing_vat_number' => '12345678-1-12',
        'billing_postcode' => '1111',
        'billing_city' => 'Saved City',
        'billing_address_1' => 'Saved Address 1',
        'billing_country' => 'Magyarország',
        'phone' => '+36201234567',
    ]);

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

    Livewire::actingAs($user)
        ->test(CheckOut::class)
        ->assertSet('data.billing_name', 'Saved Name')
        ->assertSet('data.billing_company_name', 'Saved Company')
        ->assertSet('data.billing_vat_number', '12345678-1-12')
        ->assertSet('data.billing_postcode', '1111')
        ->assertSet('data.billing_city', 'Saved City')
        ->assertSet('data.billing_address_1', 'Saved Address 1')
        ->assertSet('data.billing_phone', '+36201234567');
});

it('saves billing and shipping data to user after successful order when checkbox is checked', function () {
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
        ->set('data.billing_name', 'New Billing Name')
        ->set('data.billing_email', 'test@example.com')
        ->set('data.billing_phone', '+36309876543')
        ->set('data.billing_company_name', 'New Company')
        ->set('data.billing_vat_number', '87654321-2-21')
        ->set('data.billing_postcode', '2222')
        ->set('data.billing_city', 'New City')
        ->set('data.billing_address_1', 'New Address 1')
        ->set('data.billing_country', 'Magyarország')
        ->set('selectedShippingMethod', $shippingMethod->id)
        ->set('selectedPaymentMethod', 'bacs')
        ->set('acceptTerms', true)
        ->set('saveDataForFuture', true)
        ->call('create');

    $user->refresh();

    expect($user->billing_name)->toBe('New Billing Name');
    expect($user->phone)->toBe('+36309876543');
    expect($user->billing_company_name)->toBe('New Company');
    expect($user->billing_vat_number)->toBe('87654321-2-21');
    expect($user->billing_postcode)->toBe('2222');
    expect($user->billing_city)->toBe('New City');
    expect($user->billing_address_1)->toBe('New Address 1');
});

it('does not save billing data to user when checkbox is unchecked', function () {
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
        ->set('data.billing_name', 'New Billing Name')
        ->set('data.billing_email', 'test@example.com')
        ->set('data.billing_phone', '+36309876543')
        ->set('data.billing_postcode', '2222')
        ->set('data.billing_city', 'New City')
        ->set('data.billing_address_1', 'New Address 1')
        ->set('data.billing_country', 'Magyarország')
        ->set('selectedShippingMethod', $shippingMethod->id)
        ->set('selectedPaymentMethod', 'bacs')
        ->set('acceptTerms', true)
        ->set('saveDataForFuture', false)
        ->call('create');

    $user->refresh();

    expect($user->billing_name)->toBeNull();
    expect($user->phone)->toBeNull();
});

it('allows guest checkout without login', function () {
    $cart = Cart::factory()->create([
        'user_id' => null,
        'session_id' => session()->getId(),
    ]);

    $product = Product::factory()->create();
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $shippingMethod = ShippingMethod::factory()->create();

    Livewire::test(CheckOut::class)
        ->set('data.billing_name', 'Guest User')
        ->set('data.billing_email', 'guest@example.com')
        ->set('data.billing_phone', '+36301234567')
        ->set('data.billing_postcode', '1234')
        ->set('data.billing_city', 'Budapest')
        ->set('data.billing_address_1', 'Test Street 1')
        ->set('data.billing_country', 'Magyarország')
        ->set('selectedShippingMethod', $shippingMethod->id)
        ->set('selectedPaymentMethod', 'bacs')
        ->set('acceptTerms', true)
        ->call('create');

    $order = Order::whereNull('user_id')->first();
    expect($order)->not->toBeNull();
    expect($order->billing_name)->toBe('Guest User');
    expect($order->billing_email)->toBe('guest@example.com');
});

it('creates account for guest when registration checkbox is checked', function () {
    $cart = Cart::factory()->create([
        'user_id' => null,
        'session_id' => session()->getId(),
    ]);

    $product = Product::factory()->create();
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $shippingMethod = ShippingMethod::factory()->create();

    Livewire::test(CheckOut::class)
        ->set('data.billing_name', 'New User')
        ->set('data.billing_email', 'newuser@example.com')
        ->set('data.billing_phone', '+36301234567')
        ->set('data.billing_postcode', '1234')
        ->set('data.billing_city', 'Budapest')
        ->set('data.billing_address_1', 'Test Street 1')
        ->set('data.billing_country', 'Magyarország')
        ->set('selectedShippingMethod', $shippingMethod->id)
        ->set('selectedPaymentMethod', 'bacs')
        ->set('acceptTerms', true)
        ->set('createAccount', true)
        ->call('create');

    // Check user was created
    $newUser = User::where('email', 'newuser@example.com')->first();
    expect($newUser)->not->toBeNull();
    expect($newUser->name)->toBe('New User');
    expect($newUser->billing_name)->toBe('New User');

    // Check order is linked to new user
    $order = Order::where('user_id', $newUser->id)->first();
    expect($order)->not->toBeNull();
    expect($order->billing_email)->toBe('newuser@example.com');
});

it('sends order confirmation email to customer after successful order', function () {
    Mail::fake();

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
        ->set('data.billing_email', 'customer@example.com')
        ->set('data.billing_phone', '+36301234567')
        ->set('data.billing_postcode', '1234')
        ->set('data.billing_city', 'Budapest')
        ->set('data.billing_address_1', 'Test Street 1')
        ->set('data.billing_country', 'Magyarország')
        ->set('selectedShippingMethod', $shippingMethod->id)
        ->set('selectedPaymentMethod', 'bacs')
        ->set('acceptTerms', true)
        ->call('create');

    Mail::assertQueued(OrderConfirmationMail::class, function ($mail) {
        return $mail->hasTo('customer@example.com');
    });
});

it('sends new order notification email to admin when admin email is configured', function () {
    Mail::fake();
    config(['shop.admin_email' => 'admin@shop.com']);

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
        ->set('data.billing_email', 'customer@example.com')
        ->set('data.billing_phone', '+36301234567')
        ->set('data.billing_postcode', '1234')
        ->set('data.billing_city', 'Budapest')
        ->set('data.billing_address_1', 'Test Street 1')
        ->set('data.billing_country', 'Magyarország')
        ->set('selectedShippingMethod', $shippingMethod->id)
        ->set('selectedPaymentMethod', 'bacs')
        ->set('acceptTerms', true)
        ->call('create');

    Mail::assertQueued(NewOrderNotificationMail::class, function ($mail) {
        return $mail->hasTo('admin@shop.com');
    });
});

it('does not send admin notification when admin email is default placeholder', function () {
    Mail::fake();
    config(['shop.admin_email' => 'admin@example.com']);

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
        ->set('data.billing_email', 'customer@example.com')
        ->set('data.billing_phone', '+36301234567')
        ->set('data.billing_postcode', '1234')
        ->set('data.billing_city', 'Budapest')
        ->set('data.billing_address_1', 'Test Street 1')
        ->set('data.billing_country', 'Magyarország')
        ->set('selectedShippingMethod', $shippingMethod->id)
        ->set('selectedPaymentMethod', 'bacs')
        ->set('acceptTerms', true)
        ->call('create');

    Mail::assertQueued(OrderConfirmationMail::class);
    Mail::assertNotQueued(NewOrderNotificationMail::class);
});
