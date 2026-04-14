<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentFollowupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Booking $booking,
        public readonly array   $term,
        public readonly int     $daysUntilDue,
    ) {}

    public function envelope(): Envelope
    {
        $urgency = $this->daysUntilDue === 1 ? '⚠️ Due Tomorrow' : '📅 Due in 7 Days';

        return new Envelope(
            subject: "{$urgency}: Installment Payment — {$this->booking->booking_number}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-followup');
    }

    public function attachments(): array
    {
        return [];
    }
}
