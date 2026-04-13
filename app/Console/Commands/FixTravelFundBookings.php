<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixTravelFundBookings extends Command
{
    protected $signature = 'bookings:fix-travel-fund {--remove-duplicates : Also remove duplicate imported bookings}';
    protected $description = 'Fix travel-fund bookings that were incorrectly imported with pending installments';

    public function handle(): int
    {
        $fixed = 0;

        // Fix installment bookings where monthly terms are all ₱0 or downpayment covers total
        $bookings = Booking::whereNotNull('installment_schedule')
            ->where('payment_method', 'installment')
            ->get();

        foreach ($bookings as $booking) {
            $schedule = $booking->installment_schedule;
            if (!is_array($schedule) || empty($schedule)) continue;

            $monthlyTerms = array_filter($schedule, fn($t) => ($t['type'] ?? '') === 'installment');

            $allZero = !empty($monthlyTerms) && collect($monthlyTerms)->every(
                fn($t) => (float) ($t['amount'] ?? 0) === 0.0
            );

            $dpCoversAll = $booking->downpayment_amount !== null
                && (float) $booking->downpayment_amount >= (float) $booking->total_amount;

            if (!$allZero && !$dpCoversAll) continue;

            $downpayment = array_values(
                array_filter($schedule, fn($t) => ($t['type'] ?? '') === 'downpayment')
            );

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

        $this->info("Fixed {$fixed} installment booking(s).");

        // Remove duplicates if flag is passed
        if ($this->option('remove-duplicates')) {
            $dupes = 0;

            $duplicates = Booking::select('contact_name', 'tour_id', 'tour_date', 'total_guests')
                ->selectRaw('COUNT(*) as cnt, MIN(id) as keep_id')
                ->groupBy('contact_name', 'tour_id', 'tour_date', 'total_guests')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicates as $dup) {
                $deleted = Booking::where('contact_name', $dup->contact_name)
                    ->where('tour_id', $dup->tour_id)
                    ->whereDate('tour_date', $dup->tour_date)
                    ->where('total_guests', $dup->total_guests)
                    ->where('id', '!=', $dup->keep_id)
                    ->delete();

                if ($deleted) {
                    $this->info("Removed {$deleted} duplicate(s): {$dup->contact_name} (tour {$dup->tour_id})");
                    $dupes += $deleted;
                }
            }

            $this->info("Removed {$dupes} duplicate booking(s). Recalculating slot counts...");

            // Recalculate booked_seats for all schedules
            DB::statement("
                UPDATE tour_schedules ts
                SET ts.booked_seats = (
                    SELECT COALESCE(SUM(b.total_guests), 0)
                    FROM bookings b
                    WHERE b.schedule_id = ts.id
                      AND b.status = 'confirmed'
                      AND b.deleted_at IS NULL
                )
            ");
            $this->info("Slot counts recalculated.");
        }

        return 0;
    }
}
