<?php

namespace App\Console\Commands;

use App\Mail\PaymentFollowupMail;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentFollowupEmails extends Command
{
    protected $signature   = 'bookings:payment-followup';
    protected $description = 'Send installment payment reminders due in 1 or 7 days';

    public function handle(): int
    {
        $today = Carbon::today();
        $sent  = 0;
        $skip  = 0;

        $bookings = Booking::with('tour')
            ->where('payment_method', 'installment')
            ->whereNotIn('status', ['cancelled', 'completed', 'refunded'])
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('installment_schedule')
            ->get();

        foreach ($bookings as $booking) {
            foreach ($booking->installment_schedule ?? [] as $term) {
                if (($term['status'] ?? '') === 'paid') {
                    continue;
                }

                $dueDate     = Carbon::parse($term['due_date']);
                $daysUntilDue = (int) $today->diffInDays($dueDate, false); // negative = overdue

                if (!in_array($daysUntilDue, [1, 7])) {
                    continue;
                }

                try {
                    Mail::to($booking->contact_email)
                        ->send(new PaymentFollowupMail($booking, $term, $daysUntilDue));
                    $sent++;
                    $this->line("  ✓ Sent to {$booking->contact_email} [{$booking->booking_number} — Term {$term['term']}] (due in {$daysUntilDue}d)");
                } catch (\Throwable $e) {
                    $skip++;
                    Log::error(
                        "Payment follow-up failed for {$booking->booking_number}: " . $e->getMessage(),
                        ['exception' => $e]
                    );
                    $this->warn("  ✗ Failed: {$booking->booking_number} — " . $e->getMessage());
                }
            }
        }

        $this->info("Done. Sent: {$sent}, Failed: {$skip}.");

        return self::SUCCESS;
    }
}
