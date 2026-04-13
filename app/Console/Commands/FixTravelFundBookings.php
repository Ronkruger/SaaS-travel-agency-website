<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class FixTravelFundBookings extends Command
{
    protected $signature = 'bookings:fix-travel-fund';
    protected $description = 'Fix travel-fund bookings that were incorrectly imported with pending installments';

    public function handle(): int
    {
        $bookings = Booking::whereNotNull('installment_schedule')
            ->where('payment_method', 'installment')
            ->get();

        $fixed = 0;

        foreach ($bookings as $booking) {
            $schedule = $booking->installment_schedule;
            if (!is_array($schedule) || empty($schedule)) continue;

            $monthlyTerms = array_filter($schedule, fn($t) => ($t['type'] ?? '') === 'installment');

            // Case 1: Downpayment covers full amount — all monthly terms are ₱0.00
            $allZero = !empty($monthlyTerms) && collect($monthlyTerms)->every(
                fn($t) => (float) ($t['amount'] ?? 0) === 0.0
            );

            // Case 2: Downpayment equals total_amount (no remaining balance)
            $dpCoversAll = $booking->downpayment_amount !== null
                && (float) $booking->downpayment_amount >= (float) $booking->total_amount;

            if (!$allZero && !$dpCoversAll) continue;

            // Remove zero-amount terms, keep only downpayment
            $downpayment = array_values(
                array_filter($schedule, fn($t) => ($t['type'] ?? '') === 'downpayment')
            );

            // Mark downpayment as paid
            foreach ($downpayment as &$term) {
                $term['status'] = 'paid';
                $term['paid_at'] = $term['paid_at'] ?? now()->toDateString();
            }

            $booking->installment_schedule = $downpayment ?: null;
            $booking->payment_status = 'paid';
            $booking->payment_method = 'cash';
            $booking->save();

            $this->info("Fixed: {$booking->booking_number} - {$booking->contact_name}");
            $fixed++;
        }

        $this->info("Done. Fixed {$fixed} booking(s).");
        return 0;
    }
}
