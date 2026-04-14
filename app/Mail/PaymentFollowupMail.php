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
        public readonly bool    $isManual = false,
    ) {}

    public function envelope(): Envelope
    {
        if ($this->daysUntilDue === 0) {
            $urgency = '🔔 Payment Due Today';
        } elseif ($this->daysUntilDue <= 5) {
            $urgency = "📅 Payment Due in {$this->daysUntilDue} Days";
        } else {
            $urgency = "📅 Upcoming Payment Reminder";
        }

        return new Envelope(
            subject: "{$urgency} — {$this->booking->booking_number}",
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
