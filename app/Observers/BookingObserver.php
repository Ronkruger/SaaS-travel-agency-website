<?php

namespace App\Observers;

use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use App\Models\TourSchedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingObserver
{
    /**
     * Auto-update TourSchedule booked_seats when a booking status changes.
     * Also fires the confirmation email when admin manually confirms a non-Xendit booking.
     */
    public function updated(Booking $booking): void
    {
        if (!$booking->wasChanged('status')) {
            return;
        }

        $oldStatus = $booking->getOriginal('status');
        $newStatus = $booking->status;

        $wasConfirmed  = $oldStatus === 'confirmed';
        $isNowConfirmed = $newStatus === 'confirmed';
        $isNowReleased  = in_array($newStatus, ['cancelled', 'refunded']);

        $schedule = $this->resolveSchedule($booking);

        if ($schedule) {
            if (!$wasConfirmed && $isNowConfirmed) {
                // Claim seats — count total pax for the booking
                $pax = max(1, (int) ($booking->total_guests ?? ($booking->adults + $booking->children)));
                $schedule->increment('booked_seats', $pax);

                // Auto-mark sold out if no seats left
                if ($schedule->fresh()->remaining_seats <= 0) {
                    $schedule->update(['status' => 'sold_out']);
                }
            } elseif ($wasConfirmed && $isNowReleased) {
                // Release seats back
                $pax       = max(1, (int) ($booking->total_guests ?? ($booking->adults + $booking->children)));
                $newBooked = max(0, $schedule->booked_seats - $pax);

                $schedule->update([
                    'booked_seats' => $newBooked,
                    'status'       => $newBooked < $schedule->available_seats ? 'active' : 'sold_out',
                ]);
            }
        }

        // Send confirmation email when admin manually confirms cash/installment booking
        if ($isNowConfirmed && !$wasConfirmed && $booking->payment_method !== 'xendit') {
            try {
                Mail::to($booking->contact_email)
                    ->send(new BookingConfirmationMail($booking));
            } catch (\Throwable $e) {
                Log::warning('Booking confirmation email failed after admin approval', [
                    'booking_id' => $booking->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function resolveSchedule(Booking $booking): ?TourSchedule
    {
        // Prefer explicit schedule_id link
        if ($booking->schedule_id) {
            return TourSchedule::find($booking->schedule_id);
        }

        // Fallback: match by tour_id + departure_date = tour_date
        if ($booking->tour_date && $booking->tour_id) {
            return TourSchedule::where('tour_id', $booking->tour_id)
                ->whereDate('departure_date', $booking->tour_date)
                ->first();
        }

        return null;
    }
}
