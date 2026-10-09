<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Mail\NewOrderNotificationMail;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusChangedMail;
use App\Mail\OrderStatusChangedNotificationMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    Mail::fake();
    config(['shop.admin_email' => 'gs@gordulo-simmering.hu']);
});

it('sends no status emails for a new order', function (): void {
    Order::factory()->create(['order_status' => OrderStatus::PENDING]);

    Mail::assertNothingQueued();
});

it('emails the customer and gs@ when an order moves to a customer-facing status', function (): void {
    $order = Order::factory()->create(['order_status' => OrderStatus::PENDING, 'billing_email' => 'vevo@example.com']);

    $order->update(['order_status' => OrderStatus::COMPLETED]);

    Mail::assertQueued(OrderStatusChangedMail::class, fn (OrderStatusChangedMail $mail): bool => $mail->hasTo('vevo@example.com')
        && $mail->hasReplyTo('gs@gordulo-simmering.hu')
        && $mail->order->is($order));
    Mail::assertQueued(OrderStatusChangedNotificationMail::class, fn (OrderStatusChangedNotificationMail $mail): bool => $mail->hasTo('gs@gordulo-simmering.hu')
        && $mail->previousStatus === OrderStatus::PENDING
        && $mail->customerNotified);
});

it('tells only gs@ about internal status changes', function (OrderStatus $from, OrderStatus $to): void {
    $order = Order::factory()->create(['order_status' => $from]);

    $order->update(['order_status' => $to]);

    Mail::assertNotQueued(OrderStatusChangedMail::class);
    Mail::assertQueued(OrderStatusChangedNotificationMail::class, fn (OrderStatusChangedNotificationMail $mail): bool => ! $mail->customerNotified);
})->with([
    'moved to trash' => [OrderStatus::COMPLETED, OrderStatus::TRASH],
    'back to pending' => [OrderStatus::PROCESSING, OrderStatus::PENDING],
]);

it('sends nothing when an order is saved without a status change', function (): void {
    $order = Order::factory()->create(['order_status' => OrderStatus::PROCESSING]);

    $order->update(['shipping_city' => 'Budapest']);

    Mail::assertNothingQueued();
});

it('lets the admin skip the customer email for one change while gs@ is still told', function (bool $notify, bool $customerEmailed): void {
    actingAs(User::factory()->create(['is_admin' => true]));
    $order = Order::factory()->create(['order_status' => OrderStatus::PENDING, 'billing_email' => 'vevo@example.com']);

    Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
        ->assertFormSet(['notify_customer' => true])
        ->fillForm(['order_status' => OrderStatus::PROCESSING->value, 'notify_customer' => $notify])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->refresh()->order_status)->toBe(OrderStatus::PROCESSING);
    $customerEmailed
        ? Mail::assertQueued(OrderStatusChangedMail::class)
        : Mail::assertNotQueued(OrderStatusChangedMail::class);
    Mail::assertQueued(OrderStatusChangedNotificationMail::class, fn (OrderStatusChangedNotificationMail $mail): bool => $mail->customerNotified === $customerEmailed);
})->with([
    'ticked' => [true, true],
    'unticked' => [false, false],
]);

it('writes the status emails in Hungarian with the order details', function (): void {
    $order = Order::factory()->create(['order_status' => OrderStatus::PENDING, 'billing_name' => 'Teszt Elek']);
    $order->update(['order_status' => OrderStatus::CANCELLED]);

    $customer = new OrderStatusChangedMail($order->refresh());
    $shop = new OrderStatusChangedNotificationMail($order, OrderStatus::PENDING, true);

    expect($customer->envelope()->subject)->toBe('#' . $order->id . ' – Rendelése: Törölve')
        ->and($customer->render())->toContain('Kedves <strong>Teszt Elek</strong>', 'Rendelését töröltük.', 'Rendelt termékek')
        ->and($shop->envelope()->subject)->toBe('#' . $order->id . ' – Rendelés státusza: Feldolgozásra vár → Törölve')
        ->and($shop->render())->toContain('korábban: Feldolgozásra vár', 'A vevő értesítést kapott:', route('filament.admin.resources.orders.edit', $order));
});

it('has a customer message for every status except the internal ones', function (): void {
    foreach (OrderStatus::cases() as $status) {
        expect($status->customerMessage() === null)->toBe(in_array($status, [OrderStatus::PENDING, OrderStatus::TRASH], true));
    }
});

it('emails the customer when the order is handed to the courier or ready for pickup', function (OrderStatus $status): void {
    $order = Order::factory()->create(['order_status' => OrderStatus::PROCESSING, 'billing_email' => 'vevo@example.com']);

    $order->update(['order_status' => $status]);

    Mail::assertQueued(OrderStatusChangedMail::class, fn (OrderStatusChangedMail $mail): bool => $mail->hasTo('vevo@example.com'));
})->with([
    'handed to the courier' => [OrderStatus::SHIPPED],
    'ready for pickup' => [OrderStatus::READY_FOR_PICKUP],
]);

it('gives the tracking number and the GLS parcel point in the handed to the courier email', function (): void {
    $order = Order::factory()->create([
        'order_status' => OrderStatus::PROCESSING,
        'shipping_tracking_number' => 'GLS123456',
        'parcel_point_id' => '1011-ALPHAZOOKF',
        'parcel_point_name' => 'Alpha Zoo Batthyány tér',
        'parcel_point_address' => '1011 Budapest I. kerület, Batthyány tér 5-6.',
    ]);
    $order->update(['order_status' => OrderStatus::SHIPPED]);

    $mail = new OrderStatusChangedMail($order->refresh());

    expect($mail->envelope()->subject)->toBe('#' . $order->id . ' – Rendelése: Futárszolgálatnak átadva')
        ->and($mail->render())->toContain('átadtuk a futárszolgálatnak', 'GLS123456', 'Alpha Zoo Batthyány tér', 'Batthyány tér 5-6.');
});

it('leaves out the tracking number when the order has none', function (): void {
    $order = Order::factory()->create(['order_status' => OrderStatus::PROCESSING, 'shipping_tracking_number' => null]);
    $order->update(['order_status' => OrderStatus::SHIPPED]);

    expect((new OrderStatusChangedMail($order->refresh()))->render())->not->toContain('Csomagkövetési szám');
});

it('gives the store address and opening hours in the ready for pickup email', function (): void {
    $order = Order::factory()->create(['order_status' => OrderStatus::PROCESSING]);
    $order->update(['order_status' => OrderStatus::READY_FOR_PICKUP]);

    $mail = new OrderStatusChangedMail($order->refresh());

    expect($mail->envelope()->subject)->toBe('#' . $order->id . ' – Rendelése: Személyesen átvehető üzletünkben')
        ->and($mail->render())->toContain('átvehető üzletünkben', '1102 Budapest, Kőrösi Csoma S. út 18-20.', 'szombat 8:30–12:30');
});

it('puts the order number first in the order email subjects', function (): void {
    $order = Order::factory()->create();

    expect((new OrderConfirmationMail($order))->envelope()->subject)->toBe('#' . $order->id . ' – Rendelés visszaigazolás')
        ->and((new NewOrderNotificationMail($order))->envelope()->subject)->toBe('#' . $order->id . ' – Új rendelés érkezett');
});
