<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\TourSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    /* -----------------------------------------------------------------------
     | GET /admin/import
     * --------------------------------------------------------------------- */
    public function index()
    {
        return view('admin.import.index');
    }

    /* -----------------------------------------------------------------------
     | GET /admin/import/template
     * --------------------------------------------------------------------- */
    public function template()
    {
        $headers = [
            'Route Name', 'Travel Date', 'Names of Clients', 'PAX',
            'Status', 'Payment Terms', 'Package Rate Per Person',
            '1st Payment Date', 'Notes',
        ];
        $example = [
            'Route K Deluxe', 'FEB 11 - 21, 2026', 'Juan dela Cruz', '2',
            'Paid', 'Full Cash', '₱180,000.00', 'Apr 1, 2026', 'Confirmed departure',
        ];
        $csv  = implode(',', array_map([$this, 'csvCell'], $headers)) . "\r\n";
        $csv .= implode(',', array_map([$this, 'csvCell'], $example)) . "\r\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="booking-import-template.csv"',
        ]);
    }

    /* -----------------------------------------------------------------------
     | POST /admin/import/preview
     * --------------------------------------------------------------------- */
    public function preview(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $rows = $this->parseSlotTrackerCsv($path);

        if (empty($rows)) {
            return back()->withErrors(['csv_file' => 'The CSV file is empty or could not be parsed.']);
        }

        $tours    = Tour::select('id', 'title')->get()->keyBy(fn($t) => strtolower(trim($t->title)));
        $preview  = [];
        $warnings = [];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 1;

            // Strip "BUS 1 / BUS 2 / (BUS 1)" suffix for matching
            $rawRoute   = $row['route_name'];
            $matchRoute = strtolower(trim(preg_replace('/\s*(BUS\s*[\d\/]+|\(BUS\s*[\d]+\))\s*[-–\s]*/i', '', $rawRoute)));
            $matchRoute = preg_replace('/\s+/', ' ', trim($matchRoute));

            // Exact → contains → Levenshtein typo correction
            $tour = $tours->get($matchRoute)
                 ?? $tours->get(strtolower(trim($rawRoute)))
                 ?? $tours->first(fn($t) => str_contains(strtolower($t->title), $matchRoute)
                                         || str_contains($matchRoute, strtolower($t->title)));

            if (!$tour) {
                $bestDist = 4;
                $bestTour = null;
                foreach ($tours as $title => $t) {
                    $d = levenshtein($matchRoute, $title);
                    if ($d < $bestDist) { $bestDist = $d; $bestTour = $t; }
                }
                $tour = $bestTour;
            }

            $travelDate    = $this->parseDateRange($row['travel_date_raw']);
            $pax           = max(1, (int) ($row['pax'] ?: 1));
            $terms         = $this->normalizeTerms($row['terms']);
            $rate          = (float) preg_replace('/[^\d.]/', '', $row['rate'] ?? '0');
            $total         = $rate > 0 ? $rate * $pax : 0;
            $csvStatus     = trim($row['status'] ?? '');
            $bookingStatus = $this->normalizeBookingStatus($csvStatus);
            $paymentStatus = $this->derivePaymentStatus($csvStatus, $terms);

            $rowWarnings = [];
            if (!$tour) {
                $rowWarnings[] = "Tour \"{$rawRoute}\" not found — row will be skipped.";
            }
            if (!$travelDate) {
                $rowWarnings[] = 'Cannot parse travel date "' . ($row['travel_date_raw'] ?? '') . '" — row will be skipped.';
            }
            if ($rate <= 0) {
                $rowWarnings[] = 'No rate — will use tour\'s current price.';
            }

            $preview[] = [
                'row'             => $rowNum,
                'tour_id'         => $tour?->id,
                'tour_name'       => $tour?->title ?? $rawRoute,
                'travel_date'     => $travelDate?->format('Y-m-d'),
                'travel_date_raw' => $row['travel_date_raw'],
                'client_name'     => $row['client_name'],
                'pax'             => $pax,
                'booking_status'  => $bookingStatus,
                'payment_status'  => $paymentStatus,
                'terms'           => $terms,
                'rate'            => $rate,
                'total_amount'    => $total,
                'pay1_date'       => $row['pay1_date'],
                'notes'           => $row['pay2_notes'],
                'skipped'         => !$tour || !$travelDate,
                'warnings'        => $rowWarnings,
            ];

            if ($rowWarnings) {
                $warnings[] = "Row {$rowNum}: " . implode(' | ', $rowWarnings);
            }
        }

        $importable = collect($preview)->where('skipped', false)->count();
        session(['import_preview' => $preview]);

        return view('admin.import.index', compact('preview', 'warnings', 'importable'));
    }

    /* -----------------------------------------------------------------------
     | POST /admin/import/confirm
     * --------------------------------------------------------------------- */
    public function confirm(Request $request)
    {
        $preview = session('import_preview');

        if (empty($preview)) {
            return redirect()->route('admin.import.index')
                ->withErrors(['csv_file' => 'No import data found. Please upload the CSV again.']);
        }

        $created = $skipped = 0;
        $errors  = [];

        DB::transaction(function () use ($preview, &$created, &$skipped, &$errors) {
            foreach ($preview as $row) {
                if ($row['skipped']) { $skipped++; continue; }

                try {
                    $tour = Tour::find($row['tour_id']);
                    if (!$tour) { $skipped++; continue; }

                    $travelDate = Carbon::parse($row['travel_date']);
                    $schedule   = TourSchedule::firstOrCreate(
                        ['tour_id' => $tour->id, 'departure_date' => $travelDate->toDateString()],
                        ['available_seats' => 40, 'booked_seats' => 0, 'status' => 'active']
                    );

                    $rate  = $row['rate'] > 0 ? $row['rate'] : (float) ($tour->effective_price ?? 0);
                    $total = $rate * $row['pax'];

                    Booking::create([
                        'booking_number'     => Booking::generateBookingNumber(),
                        'tour_id'            => $tour->id,
                        'schedule_id'        => $schedule->id,
                        'tour_date'          => $travelDate,
                        'adults'             => $row['pax'],
                        'children'           => 0,
                        'infants'            => 0,
                        'total_guests'       => $row['pax'],
                        'price_per_adult'    => $rate,
                        'price_per_child'    => 0,
                        'subtotal'           => $total,
                        'discount_amount'    => 0,
                        'tax_amount'         => 0,
                        'total_amount'       => $total,
                        'status'             => $row['booking_status'],
                        'payment_status'     => $row['payment_status'],
                        'payment_method'     => $row['terms'],
                        'contact_name'       => $row['client_name'],
                        'contact_email'      => null,
                        'contact_phone'      => null,
                        'special_requests'   => $row['notes'] ?: null,
                        'downpayment_amount' => null,
                    ]);

                    // Sync seat count (observer only fires on update, not create)
                    if ($row['booking_status'] === 'confirmed') {
                        $schedule->increment('booked_seats', $row['pax']);
                        $schedule->refresh();
                        if ($schedule->booked_seats >= $schedule->available_seats) {
                            $schedule->update(['status' => 'sold_out']);
                        }
                    }

                    $created++;
                } catch (\Throwable $e) {
                    Log::error('BookingImport row ' . $row['row'] . ' failed', [
                        'error' => $e->getMessage(), 'row' => $row,
                    ]);
                    $errors[] = "Row {$row['row']}: " . $e->getMessage();
                    $skipped++;
                }
            }
        });

        session()->forget('import_preview');

        return redirect()->route('admin.import.index')
            ->with('success', "Import complete — {$created} booking(s) created, {$skipped} skipped.")
            ->with('import_errors', $errors);
    }

    // ── Parser ────────────────────────────────────────────────────────────────

    /**
     * Parse the DiscoverGRP slot-tracker block-format CSV.
     *
     * Column layout (0-indexed):
     *   [0] row counter (ignored)    [1] route name / meta key / empty
     *   [2] travel date / meta value [3] Names of Clients  ← KEY COLUMN
     *   [4] PAX  [5] Status  [6] Payment Terms  [7] Rate  [8] 1st Payment Date  [9] Notes
     *
     * Route block headers: col[1] has tour name, col[2] has date range, col[3] empty/BUS label.
     * Client rows:         col[3] is a non-empty, non-system string (the client name).
     * Metadata rows:       col[1] is "Total Seats", "Occupied Slots", etc. — skip unless col[3] has a name.
     */
    private function parseSlotTrackerCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) return [];

        $rows           = [];
        $currentRoute   = null;
        $currentDateRaw = null;

        $metaKeys = ['total seats', 'occupied slots', 'available slots', 'route name'];
        $monthRx  = 'jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec'
                  . '|january|february|march|april|june|july|august'
                  . '|september|october|november|december';

        while (($cols = fgetcsv($handle)) !== false) {
            $cols = array_pad($cols, 10, '');

            $c1 = trim($cols[1] ?? '');
            $c2 = trim($cols[2] ?? '');
            $c3 = trim($cols[3] ?? '');

            // Skip fully blank rows
            if (trim(implode('', $cols)) === '') continue;

            // Skip month-section headers: "FEB 2026", "MAR 2026" …
            if (preg_match('/^(' . $monthRx . ')\w*\s+\d{4}$/i', $c1)) continue;

            // Skip column-header rows
            if (strtolower($c1) === 'route name') continue;

            // ── Route block header detection ────────────────────────────────
            // c1 = tour name, c2 = date range ("FEB 11 - 21, 2026"), c3 = empty or "BUS X"
            $isDateRange  = preg_match('/\b(' . $monthRx . ')\b/i', $c2)
                         && preg_match('/\d{4}/', $c2);
            $c3IsBusLabel = preg_match('/^bus\s*[\d\/]+$/i', $c3);

            if ($c1 !== ''
                && !in_array(strtolower($c1), $metaKeys)
                && $isDateRange
                && ($c3 === '' || $c3IsBusLabel)
            ) {
                $currentRoute   = $c1;
                $currentDateRaw = $c2;
                continue;
            }

            // ── Client row ──────────────────────────────────────────────────
            if ($c3 === '') continue;
            $c3Lower = strtolower($c3);
            if (in_array($c3Lower, ['names of clients', 'route name', 'travel date'])) continue;
            if (preg_match('/^bus\s*[\d\/]+$/i', $c3)) continue;
            if (!$currentRoute || !$currentDateRaw) continue;

            $rows[] = [
                'route_name'      => $currentRoute,
                'travel_date_raw' => $currentDateRaw,
                'client_name'     => $c3,
                'pax'             => trim($cols[4] ?? ''),
                'status'          => trim($cols[5] ?? ''),
                'terms'           => trim($cols[6] ?? ''),
                'rate'            => trim($cols[7] ?? ''),
                'pay1_date'       => trim($cols[8] ?? ''),
                'pay2_notes'      => trim($cols[9] ?? ''),
            ];
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Extract the departure (start) date from a date range string.
     * "FEB 11 - 21, 2026" → Feb 11 2026
     * "MAR 18 - APRIL 2, 2026" → Mar 18 2026
     * "APR 01-16, 2026"   → Apr 01 2026
     */
    private function parseDateRange(string $range): ?Carbon
    {
        $range = trim($range);
        if ($range === '') return null;

        $monthRx = 'jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec'
                 . '|january|february|march|april|june|july|august'
                 . '|september|october|november|december';

        if (!preg_match('/\b(\d{4})\b/', $range, $ym)) return null;
        $year = $ym[1];

        if (!preg_match('/\b(' . $monthRx . ')\s+(\d{1,2})\b/i', $range, $dm)) return null;

        try {
            return Carbon::parse($dm[1] . ' ' . $dm[2] . ' ' . $year);
        } catch (\Throwable) {
            return null;
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function normalizeTerms(string $raw): string
    {
        $lower = strtolower(trim($raw));
        if (str_contains($lower, 'install')) return 'installment';
        if (str_contains($lower, 'down'))    return 'downpayment';
        if (str_contains($lower, 'cash'))    return 'cash';
        return 'cash';
    }

    private function normalizeBookingStatus(string $csvStatus): string
    {
        return strtolower(trim($csvStatus)) === 'paid' ? 'confirmed' : 'pending';
    }

    private function derivePaymentStatus(string $csvStatus, string $normalizedTerms): string
    {
        $s = strtolower(trim($csvStatus));
        if ($s === 'paid') {
            return $normalizedTerms === 'cash' ? 'paid' : 'partial';
        }
        if (str_contains($s, 'travel fund')) return 'partial';
        return 'unpaid';
    }

    private function csvCell(string $value): string
    {
        return '"' . str_replace('"', '""', $value) . '"';
    }
}

