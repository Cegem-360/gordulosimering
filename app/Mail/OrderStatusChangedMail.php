<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the customer that their order moved to a new status. Replies go to
 * the shop's address, since the sender is a no-reply mailbox.
 */
final class OrderStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Order $order,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = config('shop.admin_email');

        return new Envelope(
            replyTo: filled($replyTo) ? [new Address($replyTo)] : [],
            subject: '#' . $this->order->id . ' – Rendelése: ' . $this->order->order_status->getLabel(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-status-changed',
            with: ['status' => $this->order->order_status],
        );
    }
}
