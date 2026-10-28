<?php

declare(strict_types=1);

use App\Enums\PaymentMethod;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Livewire\CheckOut;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * A filled-in checkout of the given customer with one product in the cart.
 */
function paymentCheckout(User $user, ShippingMethod $shippingMethod): Testable
{
    $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => session()->getId()]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => Product::factory()->create()->id, 'quantity' => 1]);

    return Livewire::actingAs($user)
        ->test(CheckOut::class)
        ->set('data.customer_type', 'private')
        ->set('data.billing_name', 'Teszt Elek')
        ->set('data.billing_email', 'vevo@example.com')
        ->set('data.billing_phone', '+36301234567')
        ->set('data.billing_postcode', '1234')
        ->set('data.billing_city', 'Budapest')
        ->set('data.billing_address_1', 'Teszt utca 1.')
        ->set('data.billing_country', 'Magyarország')
        ->set('selectedShippingMethod', $shippingMethod->id)
        ->set('acceptTerms', true);
}

function personalPickup(): ShippingMethod
{
    return ShippingMethod::query()->where('name', 'pickup')->sole();
}

it('offers every payment method and marks who may use bank transfer', function (): void {
    paymentCheckout(User::factory()->create(), ShippingMethod::factory()->create())
        ->assertSee(['Banki átutalás', 'Utánvét', 'Bankkártya (üzletünkben)', 'Készpénz (üzletünkben)'])
        ->assertSee('Kizárólag átutalásos szerződéssel rendelkező partnereink részére.');
});

it('lets a partner with a transfer contract pay by bank transfer', function (): void {
    $user = User::factory()->withTransferContract()->create();

    paymentCheckout($user, ShippingMethod::factory()->create())
        ->assertSet('selectedPaymentMethod', PaymentMethod::BankTransfer)
        ->call('create')
        ->assertHasNoErrors();

    expect(Order::query()->sole())
        ->payment_method->toBe(PaymentMethod::BankTransfer)
        ->payment_method_title->toBe('Banki átutalás');
});

it('refuses bank transfer without a transfer contract', function (): void {
    $user = User::factory()->create();

    paymentCheckout($user, ShippingMethod::factory()->create())
        ->assertSet('selectedPaymentMethod', PaymentMethod::CashOnDelivery)
        ->set('selectedPaymentMethod', 'bacs')
        ->call('create')
        ->assertHasErrors('selectedPaymentMethod');

    expect(Order::query()->count())->toBe(0);
});

it('takes the in-store payments with personal pickup only', function (PaymentMethod $method): void {
    $user = User::factory()->create();

    paymentCheckout($user, ShippingMethod::factory()->create())
        ->set('selectedPaymentMethod', $method->value)
        ->call('create')
        ->assertHasErrors('selectedPaymentMethod');

    expect(Order::query()->count())->toBe(0);

    paymentCheckout($user, personalPickup())
        ->set('selectedPaymentMethod', $method->value)
        ->call('create')
        ->assertHasNoErrors();

    expect(Order::query()->sole())
        ->payment_method->toBe($method)
        ->payment_method_title->toBe($method->getLabel());
})->with([PaymentMethod::CardInStore, PaymentMethod::CashInStore]);

it('drops an in-store payment when the customer switches away from personal pickup', function (): void {
    paymentCheckout(User::factory()->create(), personalPickup())
        ->set('selectedPaymentMethod', PaymentMethod::CashInStore->value)
        ->set('selectedShippingMethod', ShippingMethod::factory()->create()->id)
        ->assertSet('selectedPaymentMethod', PaymentMethod::CashOnDelivery);
});

it('requires a payment method', function (): void {
    paymentCheckout(User::factory()->create(), ShippingMethod::factory()->create())
        ->set('selectedPaymentMethod')
        ->call('create')
        ->assertHasErrors('selectedPaymentMethod');
});

it('lets the admin tick the transfer contract on the partner form', function (): void {
    $partner = User::factory()->create();
    actingAs(User::factory()->admin()->create());

    Livewire::test(EditUser::class, ['record' => $partner->getRouteKey()])
        ->assertFormFieldExists('has_transfer_contract')
        ->fillForm(['has_transfer_contract' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($partner->refresh()->has_transfer_contract)->toBeTrue();
});
