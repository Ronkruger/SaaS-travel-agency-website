<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Booking $booking,
        public readonly ?string $termLabel = null,  // e.g. "Month 1" for installment payments
        public readonly bool    $isInstallment = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isInstallment
            ? 'Payment Received — ' . $this->termLabel . ' | ' . $this->booking->booking_number
            : 'Booking Confirmed — ' . $this->booking->booking_number;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.booking-confirmation');
    }
}
