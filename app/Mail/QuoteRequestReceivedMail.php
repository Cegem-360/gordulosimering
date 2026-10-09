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
 * Confirms an ajánlatkérés to the visitor who sent it. Replies go to the
 * shop's address, since the sender is a no-reply mailbox.
 */
final class QuoteRequestReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public QuoteRequest $quoteRequest,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = config('shop.admin_email');

        return new Envelope(
            replyTo: filled($replyTo) ? [new Address($replyTo)] : [],
            subject: 'Ajánlatkérését megkaptuk – ' . $this->quoteRequest->reference,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quote-request-received');
    }
}
