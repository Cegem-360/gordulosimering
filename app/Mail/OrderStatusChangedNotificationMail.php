<?php

declare(strict_types=1);

namespace App\Mail;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the shop (shop.admin_email, gs@) about every status change of an
 * order, internal ones included, and whether the customer was emailed.
 */
final class OrderStatusChangedNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Order $order,
        public OrderStatus $previousStatus,
        public bool $customerNotified,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '#' . $this->order->id . ' – Rendelés státusza: ' . $this->previousStatus->getLabel() . ' → ' . $this->order->order_status->getLabel(),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-status-changed-notification');
    }
}
