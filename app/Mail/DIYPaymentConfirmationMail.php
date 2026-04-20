<?php

namespace App\Mail;

use App\Models\DIYTourQuote;
use App\Models\DIYTourSession;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DIYPaymentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Payment        $payment,
        public readonly DIYTourQuote   $quote,
        public readonly DIYTourSession $session,
    ) {}

    public function envelope(): Envelope
    {
        $tourName = $this->session->latestItinerary?->tour_name ?? 'Your Custom Tour';

        return new Envelope(
            subject: 'Payment Confirmed — ' . $tourName . ' | ' . (tenant() ? (tenant()->company_name ?? tenant()->name) : config('app.name')),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.diy-payment-confirmation');
    }
}
