<?php

namespace App\Mail;

use App\Models\DIYTourQuote;
use App\Models\DIYTourSession;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DIYTourQuoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly DIYTourQuote   $quote,
        public readonly DIYTourSession $session,
    ) {}

    public function envelope(): Envelope
    {
        $tourName = $this->session->latestItinerary?->tour_name ?? 'Your Custom Tour';

        return new Envelope(
            subject: 'Your Official Quote — ' . $tourName . ' | Discover Group',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.diy-quote');
    }
}
