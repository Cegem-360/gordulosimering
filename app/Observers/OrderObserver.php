<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Mail\OrderStatusChangedMail;
use App\Mail\OrderStatusChangedNotificationMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

/**
 * Emails a status change of an existing order, however it happens (the
 * admin form today, an ERP sync later). New orders are left alone: checkout
 * sends its own confirmation and notification.
 */
final class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('order_status')) {
            return;
        }

        $previous = $order->getOriginal('order_status');
        $previous = $previous instanceof OrderStatus ? $previous : OrderStatus::tryFrom((string) $previous);

        if ($previous === null || $previous === $order->order_status) {
            return;
        }

        $notifiesCustomer = $order->sendsCustomerStatusEmail
            && $order->order_status->customerMessage() !== null
            && filled($order->billing_email);

        if ($notifiesCustomer) {
            Mail::to($order->billing_email)->send(new OrderStatusChangedMail($order));
        }

        $shopEmail = config('shop.admin_email');

        if (filled($shopEmail)) {
            Mail::to($shopEmail)->send(new OrderStatusChangedNotificationMail($order, $previous, $notifiesCustomer));
        }
    }
}
