<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingRejectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Booking $booking,
        public readonly ?string $reason = null,
        public readonly bool    $travelFundAdded = false,
        public readonly float   $travelFundAmount = 0.0,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Update — ' . $this->booking->booking_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.booking-rejection');
    }

    public function attachments(): array
    {
        return [];
    }
}
