<?php

declare(strict_types=1);

use App\Filament\Resources\DiscountGroups\Pages\CreateDiscountGroup;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Livewire\CheckOut;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\DiscountGroup;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Models\UserDiscount;
use Illuminate\Support\Number;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function customerWithDiscounts(float $basePercentage, array $groupPercentages = []): User
{
    $user = User::factory()->withBaseDiscount($basePercentage)->create();

    foreach ($groupPercentages as $code => $percentage) {
        UserDiscount::factory()->create([
            'user_id' => $user->id,
            'discount_group_id' => DiscountGroup::factory()->create(['code' => $code])->id,
            'percentage' => $percentage,
        ]);
    }

    return $user;
}

function discountedProduct(array $attributes = []): Product
{
    return Product::factory()->create([
        'net_selling_price' => 1000,
        'group_code' => 'CT',
        'is_on_sale' => false,
        'sale_percentage' => 0,
        ...$attributes,
    ]);
}

it('gives new users a 10% base discount by default', function (): void {
    $user = User::query()->create(['name' => 'Vevő', 'email' => 'vevo@example.com', 'password' => 'secret']);

    expect($user->base_discount_percentage)->toBe('10.00')
        ->and($user->fresh()->base_discount_percentage)->toBe('10.00');
});

it('charges guests the full price', function (): void {
    $product = discountedProduct();

    expect($product->hasDiscount())->toBeFalse()
        ->and($product->discount_percentage)->toBe(0.0)
        ->and($product->unit_price)->toBe(1000.0);
});

it('applies the base discount to every product of a logged-in customer', function (?string $groupCode): void {
    actingAs(customerWithDiscounts(10));

    $product = discountedProduct(['group_code' => $groupCode]);

    expect($product->hasDiscount())->toBeTrue()
        ->and($product->discount_percentage)->toBe(10.0)
        ->and($product->discounted_price)->toBe(900)
        ->and($product->unit_price)->toBe(900.0);
})->with([
    'grouped product' => ['CT'],
    'product without a group code' => [null],
]);

it('gives the largest of the base, group and sale discounts without stacking them', function (array $attributes, float $expected): void {
    actingAs(customerWithDiscounts(10, ['CT' => 25, 'FA' => 5]));

    expect(discountedProduct($attributes)->discount_percentage)->toBe($expected);
})->with([
    'group beats base' => [['group_code' => 'CT'], 25.0],
    'base beats a smaller group discount' => [['group_code' => 'FA'], 10.0],
    'base for a group the customer has no discount in' => [['group_code' => 'SM'], 10.0],
    'sale beats group' => [['group_code' => 'CT', 'is_on_sale' => true, 'sale_percentage' => 40], 40.0],
    'group beats sale' => [['group_code' => 'CT', 'is_on_sale' => true, 'sale_percentage' => 15], 25.0],
    'base beats sale' => [['group_code' => 'FA', 'is_on_sale' => true, 'sale_percentage' => 5], 10.0],
]);

it('lets a customer have no base discount', function (): void {
    actingAs(customerWithDiscounts(0, ['CT' => 20]));

    expect(discountedProduct(['group_code' => 'CT'])->discount_percentage)->toBe(20.0)
        ->and(discountedProduct(['group_code' => 'FA'])->hasDiscount())->toBeFalse();
});

it('caps the customer discount at 100%', function (): void {
    actingAs(customerWithDiscounts(150));

    expect(discountedProduct()->discounted_price)->toBe(0);
});

it('rounds the discounted price to whole forints', function (): void {
    actingAs(customerWithDiscounts(30));

    expect(discountedProduct(['net_selling_price' => 45])->discounted_price)->toBe(32);
});

it('stores the customer discounted price on the order items', function (): void {
    $user = customerWithDiscounts(10, ['CT' => 25]);
    $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => session()->getId()]);
    $grouped = discountedProduct(['group_code' => 'CT']);
    $other = discountedProduct(['group_code' => 'FA', 'net_selling_price' => 2000]);

    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $grouped->id, 'quantity' => 2]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $other->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(CheckOut::class)
        ->set('data.customer_type', 'private')
        ->set('data.billing_name', 'Test User')
        ->set('data.billing_email', 'test@example.com')
        ->set('data.billing_phone', '+36301234567')
        ->set('data.billing_postcode', '1234')
        ->set('data.billing_city', 'Budapest')
        ->set('data.billing_address_1', 'Test Street 1')
        ->set('data.billing_country', 'Magyarország')
        ->set('selectedShippingMethod', ShippingMethod::factory()->create()->id)
        ->set('selectedPaymentMethod', 'bacs')
        ->set('acceptTerms', true)
        ->call('create');

    $items = Order::query()->where('user_id', $user->id)->sole()->orderItems;

    expect($items->firstWhere('product_id', $grouped->id)->total)->toBe('750.00')
        ->and($items->firstWhere('product_id', $grouped->id)->subtotal)->toBe('1500.00')
        ->and($items->firstWhere('product_id', $other->id)->total)->toBe('1800.00');
});

it('shows the struck-through price and the discount on the product page', function (): void {
    actingAs(customerWithDiscounts(10, ['CT' => 25]));

    $product = discountedProduct(['is_web_visible' => true]);

    get(route('products.show', $product->slug))
        ->assertSuccessful()
        ->assertSeeInOrder([Number::currency(1000, 'HUF', 'hu', 0), Number::currency(750, 'HUF', 'hu', 0), '-25%'])
        ->assertSeeHtml('line-through');
});

it('pre-fills every discount group and reflects added and removed groups', function (): void {
    actingAs(User::factory()->admin()->create());

    $customer = customerWithDiscounts(10, ['CT' => 20]);
    DiscountGroup::factory()->create(['code' => 'FA']);

    Livewire::test(EditUser::class, ['record' => $customer->getRouteKey()])
        ->assertSchemaStateSet(function (array $state): void {
            expect($state['group_discounts'])->toHaveKeys(['CT', 'FA'])
                ->and((float) $state['group_discounts']['CT'])->toBe(20.0)
                ->and($state['group_discounts']['FA'])->toBeNull();
        });
});

it('edits the base and group discounts of a user in the admin', function (): void {
    actingAs(User::factory()->admin()->create());

    $customer = customerWithDiscounts(10, ['CT' => 20]);
    DiscountGroup::factory()->create(['code' => 'FA']);
    $password = $customer->password;

    Livewire::test(EditUser::class, ['record' => $customer->getRouteKey()])
        ->fillForm([
            'base_discount_percentage' => 12,
            'group_discounts' => ['CT' => null, 'FA' => 30],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $customer->refresh();

    expect($customer->base_discount_percentage)->toBe('12.00')
        ->and($customer->password)->toBe($password)
        ->and($customer->discounts()->with('discountGroup')->get()->mapWithKeys(
            fn (UserDiscount $discount): array => [$discount->discountGroup->code => $discount->percentage],
        )->all())->toBe(['FA' => '30.00']);
});

it('rejects percentages outside 0-100', function (): void {
    actingAs(User::factory()->admin()->create());

    $customer = customerWithDiscounts(10);
    DiscountGroup::factory()->create(['code' => 'CT']);

    Livewire::test(EditUser::class, ['record' => $customer->getRouteKey()])
        ->fillForm([
            'base_discount_percentage' => 101,
            'group_discounts' => ['CT' => 150],
        ])
        ->call('save')
        ->assertHasFormErrors([
            'base_discount_percentage',
            'group_discounts.CT',
        ]);
});

it('lets the admin add discount groups by hand', function (): void {
    actingAs(User::factory()->admin()->create());

    get('admin/discount-groups')->assertSuccessful();

    Livewire::test(CreateDiscountGroup::class)
        ->fillForm(['code' => 'EGYEDI', 'name' => 'Egyedi csoport'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(DiscountGroup::query()->where('code', 'EGYEDI')->value('name'))->toBe('Egyedi csoport');
});
