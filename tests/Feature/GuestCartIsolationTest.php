<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;

it('gives every visitor a cart of their own', function (): void {
    $bearing = Product::factory()->create();

    session()->setId(str_repeat('a', 40));
    resolve(CartService::class)->addItem($bearing->id, 1);

    session()->setId(str_repeat('b', 40));
    expect(resolve(CartService::class)->getCartItems())->toBeEmpty();

    session()->setId(str_repeat('a', 40));
    expect(resolve(CartService::class)->getCartItems())->toHaveCount(1);
});

it('keeps a visitor away from a signed-in customer\'s cart', function (): void {
    $bearing = Product::factory()->create();
    $customer = User::factory()->create();

    Auth::login($customer);
    resolve(CartService::class)->addItem($bearing->id, 1);
    Auth::logout();

    expect(resolve(CartService::class)->getCartItems())->toBeEmpty();
});

it('keeps a signed-in customer away from a visitor\'s cart', function (): void {
    $bearing = Product::factory()->create();

    resolve(CartService::class)->addItem($bearing->id, 1);

    Auth::login(User::factory()->create());

    expect(resolve(CartService::class)->getCartItems())->toBeEmpty();
});

it('lets a visitor\'s cart page see only their own items', function (): void {
    $bearing = Product::factory()->create(['name' => 'Másik látogató csapágya']);

    session()->setId(str_repeat('a', 40));
    resolve(CartService::class)->addItem($bearing->id, 1);

    $this->get(route('cart'))->assertDontSee('Másik látogató csapágya');
});
