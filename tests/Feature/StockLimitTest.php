<?php

declare(strict_types=1);

use App\Livewire\CartItem;
use App\Livewire\CheckOut;
use App\Livewire\ProductCard;
use App\Livewire\Products\Show;
use App\Models\Cart;
use App\Models\CartItem as CartItemModel;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\CartService;
use Livewire\Livewire;

function cartQuantityOf(Product $product): ?int
{
    return resolve(CartService::class)->getItem($product->id)?->quantity;
}

it('works out the most the cart takes from the stock, in whole order units', function (float $stock, ?int $unit, int $maximum): void {
    $product = Product::factory()->make(['stock_quantity' => $stock, 'order_unit' => $unit]);

    expect($product->maximumCartQuantity())->toBe($maximum);
})->with([
    'one in stock' => [1, null, 1],
    'fractional stock' => [12.5, null, 12],
    'nothing in stock' => [0, null, 0],
    'negative stock from the ERP' => [-3, null, 0],
    'order unit of ten, 25 in stock' => [25, 10, 20],
    'order unit above the stock' => [5, 10, 0],
]);

it('does not put more than the stock into the cart from the product page', function (): void {
    $product = Product::factory()->create(['stock_quantity' => 1, 'order_unit' => 0, 'quantity_unit' => 'db']);

    Livewire::test(Show::class, ['product' => $product])
        ->call('increment')
        ->assertSet('quantity', 2)
        ->assertSee('Raktáron csak 1 db van, legfeljebb ennyit rendelhet. Nagyobb mennyiségre kérjen árajánlatot!')
        ->call('addToCart')
        ->assertNotified('Nincs ennyi raktáron.');

    expect(cartQuantityOf($product))->toBeNull();
});

it('adds the stock itself from the product page without a warning', function (): void {
    $product = Product::factory()->create(['stock_quantity' => 1, 'order_unit' => 0]);

    Livewire::test(Show::class, ['product' => $product])
        ->assertDontSee('legfeljebb ennyit rendelhet')
        ->call('addToCart')
        ->assertNotified('A termék sikeresen hozzáadva a kosárhoz.');

    expect(cartQuantityOf($product))->toBe(1);
});

it('counts what is already in the cart against the stock', function (): void {
    $product = Product::factory()->create(['stock_quantity' => 1, 'order_unit' => 0]);
    resolve(CartService::class)->addItem($product->id, 1);

    Livewire::test(Show::class, ['product' => $product])
        ->call('addToCart')
        ->assertNotified('A kosárban már van ebből a termékből.');

    Livewire::test(ProductCard::class, ['product' => $product])
        ->call('addToCart')
        ->assertNotified('Nincs több raktáron.');

    expect(cartQuantityOf($product))->toBe(1);
});

it('stops the cart at the stock and says to ask for a quote', function (): void {
    $product = Product::factory()->create(['stock_quantity' => 2, 'order_unit' => 0, 'quantity_unit' => 'db']);
    resolve(CartService::class)->addItem($product->id, 1);

    Livewire::test(CartItem::class, ['productId' => $product->id, 'quantity' => 1])
        ->call('increaseQuantity')->assertSet('quantity', 2)
        ->call('increaseQuantity')->assertSet('quantity', 2)
        ->assertNotified('Nincs ennyi raktáron.')
        ->set('quantity', 7)->assertSet('quantity', 2)
        ->assertSeeHtml('max="2"');

    expect(cartQuantityOf($product))->toBe(2);
});

it('cuts a quantity added through the cart service to the stock', function (): void {
    $product = Product::factory()->create(['stock_quantity' => 3, 'order_unit' => 0]);

    expect(resolve(CartService::class)->addItem($product->id, 5))->toBeFalse()
        ->and(cartQuantityOf($product))->toBe(3);
});

it('does not take an order over the stock when the stock fell after carting', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 1, 'order_unit' => 0]);
    $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => session()->getId()]);
    CartItemModel::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 3]);

    Livewire::actingAs($user)
        ->test(CheckOut::class)
        ->set('data.customer_type', 'private')
        ->set('data.billing_name', 'Teszt Elek')
        ->set('data.billing_email', 'vevo@example.com')
        ->set('data.billing_phone', '+36301234567')
        ->set('data.billing_postcode', '1234')
        ->set('data.billing_city', 'Budapest')
        ->set('data.billing_address_1', 'Teszt utca 1.')
        ->set('data.billing_country', 'Magyarország')
        ->set('selectedShippingMethod', ShippingMethod::factory()->create()->id)
        ->set('acceptTerms', true)
        ->call('create')
        ->assertNotified($product->name . ': a készlet időközben csökkent.');

    expect(Order::query()->count())->toBe(0);
});
