<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\{Customer, LoyaltyTransaction};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoyaltyPointsEarned extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Customer $customer,
        public LoyaltyTransaction $transaction,
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You Earned Loyalty Points!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.loyalty.points-earned',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
