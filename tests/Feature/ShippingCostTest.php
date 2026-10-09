<?php

declare(strict_types=1);

use App\Filament\Resources\ShippingMethods\Pages\EditShippingMethod;
use App\Livewire\CheckOut;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Number;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/**
 * A logged-in checkout with a cart of products of the given weights (kg, or
 * null for no weight), one of each.
 *
 * @param  array<int, float|null>  $weights
 */
function checkoutWithWeights(array $weights): Testable
{
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => session()->getId()]);

    foreach ($weights as $weight) {
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => Product::factory()->create(['weight' => $weight])->id,
            'quantity' => 1,
        ]);
    }

    return Livewire::actingAs($user)
        ->test(CheckOut::class)
        ->set('data.billing_name', 'Teszt Elek')
        ->set('data.billing_email', 'vevo@example.com')
        ->set('data.billing_phone', '+36301234567')
        ->set('data.billing_postcode', '1234')
        ->set('data.billing_city', 'Budapest')
        ->set('data.billing_address_1', 'Teszt utca 1.')
        ->set('data.billing_country', 'Magyarország')
        ->set('acceptTerms', true);
}

/**
 * @return array{id: string, name: string, contact: array{countryCode: string, postalCode: string, city: string, address: string}}
 */
function glsParcelPoint(): array
{
    return [
        'id' => '1011-ALPHAZOOKF',
        'name' => 'Alpha Zoo Batthyány tér',
        'contact' => [
            'countryCode' => 'HU',
            'postalCode' => '1011',
            'city' => 'Budapest I. kerület',
            'address' => 'Batthyány tér 5-6.',
        ],
    ];
}

beforeEach(function (): void {
    Mail::fake();
});

it('charges the GLS rate of the weight band and payment method, plus VAT', function (float $weight, string $paymentMethod, int $expectedCost): void {
    $gls = ShippingMethod::factory()->glsRates()->create();

    expect($gls->costFor($weight, $paymentMethod))->toBe($expectedCost);
})->with([
    'up to 3 kg by transfer' => [3.0, 'bacs', 3239],
    'up to 3 kg cash on delivery' => [0.5, 'cod', 4509],
    '3-15 kg by transfer' => [3.001, 'bacs', 3747],
    '3-15 kg cash on delivery' => [15.0, 'cod', 5017],
    '15-30 kg by transfer' => [30.0, 'bacs', 4763],
    '15-30 kg cash on delivery' => [20.0, 'cod', 6033],
]);

it('takes no GLS order over 30 kg', function (): void {
    $gls = ShippingMethod::factory()->glsRates()->create();

    expect($gls->costFor(30.5, 'bacs'))->toBeNull()
        ->and($gls->maxWeight())->toBe(30.0);
});

it('charges the flat cost when a method has no weight rates', function (): void {
    $pickup = ShippingMethod::factory()->create(['cost' => 0, 'rates' => null]);

    expect($pickup->costFor(500, 'cod'))->toBe(0)
        ->and($pickup->maxWeight())->toBeNull();
});

it('counts products without a weight as 0 kg and follows the payment method', function (): void {
    $gls = ShippingMethod::factory()->glsRates()->create();

    checkoutWithWeights([2.0, null, 0.5])
        ->set('selectedShippingMethod', $gls->id)
        ->assertSet('cartWeight', 2.5)
        ->assertSet('shippingCost', 3239)
        ->assertSee(Number::currency(3239, in: 'HUF', locale: 'hu', precision: 0))
        ->set('selectedPaymentMethod', 'cod')
        ->assertSet('shippingCost', 4509)
        ->call('create');

    expect(Order::query()->sole()->shipping_cost)->toBe(4509);
});

it('preselects the first shipping method the cart is light enough for', function (): void {
    ShippingMethod::query()->delete();
    ShippingMethod::factory()->glsRates()->create();
    $pickup = ShippingMethod::factory()->create(['cost' => 0]);

    checkoutWithWeights([31.0])
        ->assertSet('selectedShippingMethod', $pickup->id)
        ->assertSee('30 kg feletti rendelésnél nem választható.');
});

it('refuses GLS for a cart over 30 kg', function (): void {
    $gls = ShippingMethod::factory()->glsRates()->create();

    checkoutWithWeights([20.0, 11.0])
        ->set('selectedShippingMethod', $gls->id)
        ->call('create')
        ->assertHasErrors(['selectedShippingMethod']);

    expect(Order::query()->count())->toBe(0);
});

it('asks for a GLS parcel point before placing the order', function (): void {
    $parcelPoint = ShippingMethod::factory()->parcelPoint()->create();

    checkoutWithWeights([1.0])
        ->set('selectedShippingMethod', $parcelPoint->id)
        ->assertSee('Átvevőhely kiválasztása')
        ->call('create')
        ->assertHasErrors(['parcelPoint']);

    expect(Order::query()->count())->toBe(0);
});

it('saves the GLS parcel point picked on the map with the order', function (): void {
    $parcelPoint = ShippingMethod::factory()->parcelPoint()->create();

    checkoutWithWeights([1.0])
        ->set('selectedShippingMethod', $parcelPoint->id)
        ->call('selectParcelPoint', glsParcelPoint())
        ->assertSee('Alpha Zoo Batthyány tér')
        ->call('create')
        ->assertHasNoErrors();

    expect(Order::query()->sole())
        ->parcel_point_id->toBe('1011-ALPHAZOOKF')
        ->parcel_point_name->toBe('Alpha Zoo Batthyány tér')
        ->parcel_point_address->toBe('1011 Budapest I. kerület, Batthyány tér 5-6.')
        ->shipping_cost->toBe(3239);
});

it('does not store a parcel point for home delivery', function (): void {
    $gls = ShippingMethod::factory()->glsRates()->create();

    checkoutWithWeights([1.0])
        ->set('selectedShippingMethod', $gls->id)
        ->call('selectParcelPoint', glsParcelPoint())
        ->call('create');

    expect(Order::query()->sole()->hasParcelPoint())->toBeFalse();
});

it('rejects a parcel point without an id or name', function (): void {
    $parcelPoint = ShippingMethod::factory()->parcelPoint()->create();

    checkoutWithWeights([1.0])
        ->set('selectedShippingMethod', $parcelPoint->id)
        ->call('selectParcelPoint', ['contact' => ['city' => 'Budapest']])
        ->assertHasErrors(['id', 'name'])
        ->assertSet('parcelPoint', null);
});

it('lets the admin edit the weight rates and the parcel point switch', function (): void {
    $this->actingAs(User::factory()->admin()->create());
    $method = ShippingMethod::factory()->create(['rates' => null]);

    Livewire::test(EditShippingMethod::class, ['record' => $method->getRouteKey()])
        ->fillForm([
            'requires_parcel_point' => true,
            'rates' => [
                ['max_weight' => 5, 'bank_transfer' => 1000, 'cash_on_delivery' => 2000],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($method->refresh())
        ->requires_parcel_point->toBeTrue()
        ->and($method->costFor(4, 'cod'))->toBe(2540);
});
