<?php

namespace App\Listeners;

use App\Models\ClientEmailLog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class LogSentEmail
{
    /**
     * Handle the Laravel MessageSent event.
     * Fires automatically after every Mail::to()->send() in the application.
     */
    public function handle(MessageSent $event): void
    {
        try {
            $message  = $event->message;
            $mailable = $event->data['__laravel_mailable'] ?? null;

            // Extract first "to" address
            $toAddresses = $message->getTo();
            $toAddress   = count($toAddresses) > 0 ? $toAddresses[0] : null;
            $toEmail     = $toAddress?->getAddress() ?? 'unknown';
            $toName      = $toAddress?->getName() ?: null;

            // Mail class short name (e.g. BookingConfirmationMail)
            $mailClass = $mailable ? class_basename($mailable) : null;

            // Try to resolve booking from the mailable
            $bookingId     = null;
            $bookingNumber = null;
            if ($mailable && property_exists($mailable, 'booking')) {
                $booking       = $mailable->booking;
                $bookingId     = $booking?->id;
                $bookingNumber = $booking?->booking_number;
            }

            ClientEmailLog::create([
                'to_email'       => $toEmail,
                'to_name'        => $toName,
                'subject'        => $message->getSubject(),
                'mail_class'     => $mailClass,
                'status'         => 'sent',
                'booking_id'     => $bookingId,
                'booking_number' => $bookingNumber,
            ]);
        } catch (\Throwable $e) {
            // Never let logging break email delivery
            Log::warning('ClientEmailLog: failed to record sent email — ' . $e->getMessage());
        }
    }
}
