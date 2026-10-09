<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the shop (shop.admin_email, gs@) about a new ajánlatkérés. Replying
 * answers the visitor directly.
 */
final class QuoteRequestSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public QuoteRequest $quoteRequest,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->quoteRequest->email, $this->quoteRequest->name)],
            subject: 'Új ajánlatkérés: ' . $this->quoteRequest->name . ' – ' . $this->quoteRequest->reference,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quote-request-submitted');
    }
}
