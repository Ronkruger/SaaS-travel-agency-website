<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
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
     | Show upload form & download template link
     * --------------------------------------------------------------------- */
    public function index()
    {
        return view('admin.import.index');
    }

    /* -----------------------------------------------------------------------
     | GET /admin/import/template
     | Download blank CSV template matching spreadsheet columns
     * --------------------------------------------------------------------- */
    public function template()
    {
        $headers = [
            'Route Name',
            'Travel Date',       // YYYY-MM-DD
            'Names of Clients',
            'PAX',               // integer
            'Status',            // pending / confirmed / cancelled
            'Payment Terms',     // full / installment / downpayment
            'Package Rate Per Person',
            '1st Payment Amount',
            '1st Payment Date',  // YYYY-MM-DD
            '2nd Payment Amount',
            '2nd Payment Date',  // YYYY-MM-DD
            'Contact Email',
            'Contact Phone',
        ];

        $example = [
            'Route K Deluxe',
            '2026-05-15',
            'Juan dela Cruz',
            '2',
            'confirmed',
            'full',
            '15000',
            '30000',
            '2026-04-01',
            '',
            '',
            'juan@example.com',
            '+63-912-345-6789',
        ];

        $csv = implode(',', array_map([$this, 'csvCell'], $headers)) . "\r\n";
        $csv .= implode(',', array_map([$this, 'csvCell'], $example)) . "\r\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="booking-import-template.csv"',
        ]);
    }

    /* -----------------------------------------------------------------------
     | POST /admin/import/preview
     | Parse uploaded CSV → preview table stored in session
     * --------------------------------------------------------------------- */
    public function preview(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $rows = $this->parseCsv($path);

        if (empty($rows)) {
            return back()->withErrors(['csv_file' => 'The CSV file is empty or could not be parsed.']);
        }

        $tours    = Tour::select('id', 'title')->get()->keyBy(fn($t) => strtolower(trim($t->title)));
        $preview  = [];
        $warnings = [];

        foreach ($rows as $i => $row) {
            $rowNum    = $i + 2; // 1-based, row 1 is header
            $tourName  = strtolower(trim($row['route name'] ?? ''));
            $tour      = $tours->get($tourName);

            // Fuzzy fallback: check if any tour title contains the cell value
            if (!$tour && $tourName !== '') {
                $tour = $tours->first(fn($t) => str_contains(strtolower($t->title), $tourName)
                    || str_contains($tourName, strtolower($t->title)));
            }

            $travelDate = $this->parseDate($row['travel date'] ?? '');
            $pax        = max(1, (int) ($row['pax'] ?? 1));
            $status     = $this->normalizeStatus($row['status'] ?? 'pending');
            $terms      = $this->normalizeTerms($row['payment terms'] ?? 'full');
            $rate       = (float) preg_replace('/[^\d.]/', '', $row['package rate per person'] ?? '0');
            $pay1Amt    = (float) preg_replace('/[^\d.]/', '', $row['1st payment amount'] ?? '0');
            $pay1Date   = $this->parseDate($row['1st payment date'] ?? '');
            $pay2Amt    = (float) preg_replace('/[^\d.]/', '', $row['2nd payment amount'] ?? '0');
            $pay2Date   = $this->parseDate($row['2nd payment date'] ?? '');
            $email      = strtolower(trim($row['contact email'] ?? ''));
            $phone      = trim($row['contact phone'] ?? '');
            $name       = trim($row['names of clients'] ?? 'Unknown');

            $rowWarnings = [];
            if (!$tour) {
                $rowWarnings[] = "Tour \"{$row['route name']}\" not found — row will be skipped.";
            }
            if (!$travelDate) {
                $rowWarnings[] = "Invalid travel date \"{$row['travel date']}\" — row will be skipped.";
            }
            if ($rate <= 0) {
                $rowWarnings[] = "Rate is 0 — will use tour's current price.";
            }

            $preview[] = [
                'row'          => $rowNum,
                'tour_id'      => $tour?->id,
                'tour_name'    => $tour?->title ?? ($row['route name'] ?? ''),
                'travel_date'  => $travelDate?->format('Y-m-d'),
                'client_name'  => $name,
                'pax'          => $pax,
                'status'       => $status,
                'terms'        => $terms,
                'rate'         => $rate,
                'total_amount' => $rate * $pax,
                'pay1_amount'  => $pay1Amt,
                'pay1_date'    => $pay1Date?->format('Y-m-d'),
                'pay2_amount'  => $pay2Amt,
                'pay2_date'    => $pay2Date?->format('Y-m-d'),
                'email'        => $email,
                'phone'        => $phone,
                'skipped'      => !$tour || !$travelDate,
                'warnings'     => $rowWarnings,
            ];

            if ($rowWarnings) {
                $warnings[] = "Row {$rowNum}: " . implode(' | ', $rowWarnings);
            }
        }

        $importable = collect($preview)->where('skipped', false)->count();

        // Store in session — 2048-char limit safe with 100-row CSV
        session(['import_preview' => $preview]);

        return view('admin.import.index', compact('preview', 'warnings', 'importable'));
    }

    /* -----------------------------------------------------------------------
     | POST /admin/import/confirm
     | Persist previewed rows to the database
     * --------------------------------------------------------------------- */
    public function confirm(Request $request)
    {
        $preview = session('import_preview');

        if (empty($preview)) {
            return redirect()->route('admin.import.index')
                ->withErrors(['csv_file' => 'No import data found. Please upload the CSV again.']);
        }

        $created  = 0;
        $skipped  = 0;
        $errors   = [];

        DB::transaction(function () use ($preview, &$created, &$skipped, &$errors) {
            foreach ($preview as $row) {
                if ($row['skipped']) {
                    $skipped++;
                    continue;
                }

                try {
                    $tour = Tour::find($row['tour_id']);
                    if (!$tour) {
                        $skipped++;
                        continue;
                    }

                    // Find or create the TourSchedule for this date
                    $travelDate = Carbon::parse($row['travel_date']);
                    $schedule   = TourSchedule::firstOrCreate(
                        ['tour_id' => $tour->id, 'departure_date' => $travelDate->toDateString()],
                        [
                            'available_seats' => 40,
                            'booked_seats'    => 0,
                            'status'          => 'active',
                        ]
                    );

                    $rate = $row['rate'] > 0
                        ? $row['rate']
                        : (float) ($tour->effective_price ?? 0);

                    $total = $rate * $row['pax'];

                    $booking = Booking::create([
                        'booking_number'  => Booking::generateBookingNumber(),
                        'tour_id'         => $tour->id,
                        'schedule_id'     => $schedule->id,
                        'tour_date'       => $travelDate,
                        'adults'          => $row['pax'],
                        'children'        => 0,
                        'infants'         => 0,
                        'total_guests'    => $row['pax'],
                        'price_per_adult' => $rate,
                        'price_per_child' => 0,
                        'subtotal'        => $total,
                        'discount_amount' => 0,
                        'tax_amount'      => 0,
                        'total_amount'    => $total,
                        'status'          => $row['status'],
                        'payment_status'  => $this->derivePaymentStatus($row),
                        'payment_method'  => $row['terms'],
                        'contact_name'    => $row['client_name'],
                        'contact_email'   => $row['email'] ?: null,
                        'contact_phone'   => $row['phone'] ?: null,
                        'downpayment_amount' => $row['pay1_amount'] > 0 ? $row['pay1_amount'] : null,
                    ]);

                    // Record 1st payment
                    if ($row['pay1_amount'] > 0) {
                        Payment::create([
                            'booking_id'     => $booking->id,
                            'amount'         => $row['pay1_amount'],
                            'method'         => $row['terms'] === 'installment' ? 'installment' : 'cash',
                            'status'         => 'paid',
                            'transaction_id' => 'IMPORT-' . $booking->booking_number . '-P1',
                            'paid_at'        => $row['pay1_date'] ? Carbon::parse($row['pay1_date']) : now(),
                        ]);
                    }

                    // Record 2nd payment
                    if ($row['pay2_amount'] > 0) {
                        Payment::create([
                            'booking_id'     => $booking->id,
                            'amount'         => $row['pay2_amount'],
                            'method'         => 'installment',
                            'status'         => 'paid',
                            'transaction_id' => 'IMPORT-' . $booking->booking_number . '-P2',
                            'paid_at'        => $row['pay2_date'] ? Carbon::parse($row['pay2_date']) : now(),
                        ]);
                    }

                    $created++;

                } catch (\Throwable $e) {
                    Log::error('BookingImport: row ' . $row['row'] . ' failed', [
                        'error' => $e->getMessage(),
                        'row'   => $row,
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

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return [];
        }

        $rows    = [];
        $headers = null;

        while (($line = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                // Normalize header keys: lowercase, strip BOM
                $headers = array_map(
                    fn($h) => strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h))),
                    $line
                );
                continue;
            }

            if (count($line) < 2 || implode('', $line) === '') {
                continue; // skip blank rows
            }

            $padded = array_pad($line, count($headers), '');
            $rows[] = array_combine($headers, array_slice($padded, 0, count($headers)));
        }

        fclose($handle);
        return $rows;
    }

    private function parseDate(string $value): ?Carbon
    {
        if (trim($value) === '') {
            return null;
        }
        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeStatus(string $raw): string
    {
        return match (strtolower(trim($raw))) {
            'confirmed', 'confirm', 'approved' => 'confirmed',
            'cancelled', 'canceled'             => 'cancelled',
            default                             => 'pending',
        };
    }

    private function normalizeTerms(string $raw): string
    {
        $lower = strtolower(trim($raw));
        if (str_contains($lower, 'install')) {
            return 'installment';
        }
        if (str_contains($lower, 'down')) {
            return 'downpayment';
        }
        return 'cash';
    }

    private function derivePaymentStatus(array $row): string
    {
        $paid = $row['pay1_amount'] + $row['pay2_amount'];
        if ($paid <= 0) {
            return 'unpaid';
        }
        if ($paid >= $row['total_amount'] - 0.01) {
            return 'paid';
        }
        return 'partial';
    }

    private function csvCell(string $value): string
    {
        // Escape quotes; wrap in quotes if contains comma, newline, or quote
        $escaped = str_replace('"', '""', $value);
        return '"' . $escaped . '"';
    }
}
