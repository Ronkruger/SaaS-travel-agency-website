<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $activationUrl;
    public $companyName;
    public $ownerName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $activationUrl, string $companyName, string $ownerName)
    {
        $this->activationUrl = $activationUrl;
        $this->companyName = $companyName;
        $this->ownerName = $ownerName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Activate Your 30-Day Free Trial - ' . $this->companyName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.trial-activation',
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
