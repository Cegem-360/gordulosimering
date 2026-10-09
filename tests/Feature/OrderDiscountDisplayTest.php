<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\RelationManagers\OrderItemsRelationManager;
use App\Livewire\OrderDetail;
use App\Livewire\OrderHistory;
use App\Mail\NewOrderNotificationMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Number;
use Livewire\Livewire;

function huf(float $amount): string
{
    return Number::currency($amount, in: 'HUF', locale: 'hu', precision: 0);
}

/**
 * An order of 2 × a 1 000 Ft (net) product at 10% off, as the checkout
 * saves it, and 1 × a 500 Ft product without a discount.
 */
function discountedOrder(?User $user = null): Order
{
    $order = Order::factory()->create([
        'user_id' => ($user ?? User::factory()->create())->id,
        'order_status' => OrderStatus::PENDING,
        'shipping_cost' => 1000,
    ]);

    $order->orderItems()->create([
        'product_id' => Product::factory()->create(['name' => 'SKF csapágy', 'product_code' => 'SKF-6204'])->id,
        'quantity' => 2,
        'total' => '900.00',
        'subtotal' => '1800.00',
        'total_tax' => '243.00',
        'subtotal_tax' => '486.00',
        'tax_class' => '27%',
        'regular_price' => 1000,
        'discount_percentage' => 10,
    ]);

    $order->orderItems()->create([
        'product_id' => Product::factory()->create(['name' => 'O-gyűrű'])->id,
        'quantity' => 1,
        'total' => '500.00',
        'subtotal' => '500.00',
        'total_tax' => '135.00',
        'subtotal_tax' => '135.00',
        'tax_class' => '27%',
        'regular_price' => 500,
        'discount_percentage' => 0,
    ]);

    return $order->refresh();
}

it('works out the savings, the VAT and the gross total of an order', function (): void {
    $order = discountedOrder();
    $item = $order->orderItems->firstWhere('quantity', 2);

    expect($item->hasDiscount())->toBeTrue()
        ->and($item->lineSavings())->toBe(200.0)
        ->and($item->grossLineTotal())->toBe(2286.0)
        ->and($order->savings())->toBe(200.0)
        ->and($order->orderTotal())->toEqual(2300)
        ->and($order->vatAmount())->toEqual(621)
        ->and($order->grossTotal())->toEqual(3921);
});

it('shows no discount for an item ordered before the list price was kept', function (): void {
    $item = new OrderItem(['total' => '900.00', 'subtotal' => '900.00', 'quantity' => 1, 'discount_percentage' => 0]);

    expect($item->hasDiscount())->toBeFalse()
        ->and($item->lineSavings())->toBe(0.0);
});

it('shows the list price, the discount, the VAT and the gross total on the order page', function (): void {
    $user = User::factory()->create();
    $order = discountedOrder($user);

    $this->actingAs($user);

    Livewire::test(OrderDetail::class, ['order' => $order])
        ->assertSeeHtml('line-through')
        ->assertSeeInOrder([huf(1000), huf(900), '−10%'])
        ->assertSee(['Megtakarítás:', '−' . huf(200), 'ÁFA (27%):', huf(621), huf(3921)]);
});

it('shows the discount and the gross total in the order history', function (): void {
    $user = User::factory()->create();
    discountedOrder($user);

    $this->actingAs($user);

    Livewire::test(OrderHistory::class)
        ->assertSee(['−10%', huf(3921)]);
});

it('shows the discount, the savings and the VAT in the customer email', function (): void {
    $html = (new OrderConfirmationMail(discountedOrder()))->render();

    expect($html)->toContain('Nettó listaár:', huf(1000), huf(900), '−10%', 'Megtakarítás (kedvezmény)', '−' . huf(200), 'ÁFA (27%)', huf(621), huf(3921));
});

it('shows the product code, the discount and the totals in the gs@ email', function (): void {
    $html = (new NewOrderNotificationMail(discountedOrder()))->render();

    expect($html)->toContain('Cikkszám: SKF-6204', '−10%', 'Megtakarítás (kedvezmény)', 'ÁFA (27%)', huf(3921))
        ->not->toContain('SKU:');
});

it('labels the unit and line prices the right way round and shows the gross line total in the admin', function (): void {
    $this->actingAs(User::factory()->admin()->create());
    $order = discountedOrder();
    $item = $order->orderItems->firstWhere('quantity', 2);

    Livewire::test(OrderItemsRelationManager::class, ['ownerRecord' => $order, 'pageClass' => EditOrder::class])
        ->assertTableColumnStateSet('total', '900.00', $item)
        ->assertTableColumnStateSet('subtotal', '1800.00', $item)
        ->assertTableColumnStateSet('subtotal_tax', '486.00', $item)
        ->assertTableColumnStateSet('gross_line_total', 2286.0, $item)
        ->assertSeeInOrder(['Nettó listaár', 'Kedvezmény', 'Nettó egységár', 'Nettó összesen', 'ÁFA (27%)', 'Bruttó összesen'])
        ->assertSee('−10%');
});

it('works out the line total, the VAT and the VAT class of an item on save', function (): void {
    $order = Order::factory()->create();

    $item = $order->orderItems()->create([
        'product_id' => Product::factory()->create()->id,
        'quantity' => 3,
        'total' => 1000,
    ]);

    expect($item->refresh())
        ->subtotal->toBe('3000.00')
        ->total_tax->toBe('270.00')
        ->subtotal_tax->toBe('810.00')
        ->tax_class->toBe('27%');

    $item->update(['quantity' => 1, 'total' => 500, 'subtotal' => '99999', 'total_tax' => 0]);

    expect($item->refresh())
        ->subtotal->toBe('500.00')
        ->total_tax->toBe('135.00')
        ->subtotal_tax->toBe('135.00');
});

it('lets the admin add an item with only the product and the quantity, the rest worked out', function (): void {
    $this->actingAs(User::factory()->admin()->create());
    $order = Order::factory()->create();
    $product = Product::factory()->create(['net_selling_price' => 1200]);

    Livewire::test(OrderItemsRelationManager::class, ['ownerRecord' => $order, 'pageClass' => EditOrder::class])
        ->mountTableAction('create')
        ->setTableActionData(['product_id' => $product->id, 'quantity' => 2])
        ->assertTableActionDataSet(['total' => 1200])
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    expect($order->orderItems()->sole())
        ->total->toBe('1200.00')
        ->subtotal->toBe('2400.00')
        ->subtotal_tax->toBe('648.00')
        ->tax_class->toBe('27%');
});
