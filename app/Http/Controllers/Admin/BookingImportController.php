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
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
            'csv_file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $ext  = strtolower($file->getClientOriginalExtension());

        $rows   = in_array($ext, ['xlsx', 'xls'])
            ? $this->readSpreadsheetRows($path)
            : null;
        $blocks = $this->parseSlotTrackerCsv($path, $rows);

        if (empty($blocks)) {
            return back()->withErrors(['csv_file' => 'The file is empty or could not be parsed.']);
        }

        $existingTours = Tour::select('id', 'title')
            ->get()
            ->keyBy(fn($t) => $this->normalizeRouteName($t->title));

        $preview  = [];
        $warnings = [];
        $rowNum   = 0;

        foreach ($blocks as $block) {
            $rawRoute       = $block['route_name'];
            $matchKey       = $this->normalizeRouteName($rawRoute);
            $dateRaw        = $block['travel_date_raw'];
            $totalSeats     = $block['total_seats'];
            $departure      = $this->parseDateRange($dateRaw, 'start');
            $returnDate     = $this->parseDateRange($dateRaw, 'end');
            $tour           = $this->resolveTour($matchKey, $existingTours);
            $willCreateTour = ($tour === null);
            $tourTitle      = $tour?->title ?? $this->toTitleCase($rawRoute);

            foreach ($block['clients'] as $client) {
                $rowNum++;
                $pax           = max(1, (int) ($client['pax'] ?: 1));
                $terms         = $this->normalizeTerms($client['terms']);
                $rate          = (float) preg_replace('/[^\d.]/', '', $client['rate'] ?? '0');
                $total         = $rate > 0 ? $rate * $pax : 0;
                $csvStatus     = trim($client['status'] ?? '');
                $bookingStatus = $this->normalizeBookingStatus($csvStatus);
                $paymentStatus = $this->derivePaymentStatus($csvStatus, $terms);

                $rowWarnings = [];
                if ($willCreateTour) {
                    $rowWarnings[] = "Tour not found — will be auto-created as \"{$tourTitle}\".";
                }
                if (!$departure) {
                    $rowWarnings[] = 'Cannot parse travel date "' . $dateRaw . '" — row will be skipped.';
                }
                if ($rate <= 0) {
                    $rowWarnings[] = 'No rate — will store as ₱0.';
                }

                $preview[] = [
                    'row'              => $rowNum,
                    'tour_id'          => $tour?->id,
                    'tour_name'        => $tourTitle,
                    'will_create_tour' => $willCreateTour,
                    'travel_date'      => $departure?->format('Y-m-d'),
                    'return_date'      => ($returnDate ?? $departure)?->format('Y-m-d'),
                    'travel_date_raw'  => $dateRaw,
                    'total_seats'      => $totalSeats,
                    'client_name'      => $client['client_name'],
                    'pax'              => $pax,
                    'booking_status'   => $bookingStatus,
                    'payment_status'   => $paymentStatus,
                    'terms'            => $terms,
                    'rate'             => $rate,
                    'total_amount'     => $total,
                    'pay1_date'        => $client['pay1_date'],
                    'notes'            => $client['pay2_notes'],
                    'skipped'          => !$departure,
                    'warnings'         => $rowWarnings,
                ];

                $realWarnings = array_filter($rowWarnings, fn($w) => !str_contains($w, 'auto-created'));
                if ($realWarnings) {
                    $warnings[] = "Row {$rowNum}: " . implode(' | ', $realWarnings);
                }
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
                ->withErrors(['csv_file' => 'No import data found. Please upload the file again.']);
        }

        // Build available_seats map: "normalised_tour_name|YYYY-MM-DD" => total_seats
        $scheduleSeats = [];
        foreach ($preview as $row) {
            if ($row['skipped']) continue;
            $key = $this->normalizeRouteName($row['tour_name']) . '|' . $row['travel_date'];
            if (!isset($scheduleSeats[$key])) {
                $scheduleSeats[$key] = $row['total_seats'] > 0 ? $row['total_seats'] : 40;
            }
        }

        $tourCache  = Tour::select('id', 'title')->get()->keyBy(fn($t) => $this->normalizeRouteName($t->title));
        $schedCache = [];
        $created    = $skipped = 0;
        $errors     = [];

        // Pre-calculate booking number base to avoid duplicates inside the transaction
        $year           = date('Y');
        $existingCount  = Booking::whereYear('created_at', $year)->count();
        $bookingCounter = $existingCount;

        DB::transaction(function () use ($preview, $scheduleSeats, &$tourCache, &$schedCache, &$created, &$skipped, &$errors, $year, &$bookingCounter) {
            foreach ($preview as $row) {
                if ($row['skipped']) { $skipped++; continue; }

                try {
                    // 1. Resolve or auto-create Tour
                    $normKey = $this->normalizeRouteName($row['tour_name']);
                    $tour    = $tourCache->get($normKey);

                    if (!$tour) {
                        $slug = $this->uniqueSlug($row['tour_name']);
                        $tour = Tour::create([
                            'title'                    => $row['tour_name'],
                            'slug'                     => $slug,
                            'is_active'                => true,
                            'is_featured'              => false,
                            'regular_price_per_person' => $row['rate'] > 0 ? $row['rate'] : 0,
                        ]);
                        $tourCache->put($normKey, $tour);
                    }

                    // 2. Resolve or create TourSchedule
                    $schedKey = $tour->id . '|' . $row['travel_date'];
                    if (!isset($schedCache[$schedKey])) {
                        $availSeats = $scheduleSeats[$normKey . '|' . $row['travel_date']] ?? 40;
                        $schedule   = TourSchedule::firstOrCreate(
                            ['tour_id' => $tour->id, 'departure_date' => $row['travel_date']],
                            [
                                'return_date'     => $row['return_date'] ?? $row['travel_date'],
                                'available_seats' => $availSeats,
                                'booked_seats'    => 0,
                                'status'          => 'active',
                            ]
                        );
                        // Sync available_seats from CSV even if schedule existed before
                        if (!$schedule->wasRecentlyCreated && $availSeats > 0
                            && $schedule->available_seats !== $availSeats) {
                            $schedule->update(['available_seats' => $availSeats]);
                        }
                        $schedCache[$schedKey] = $schedule;
                    }
                    $schedule = $schedCache[$schedKey];

                    // 3. Create Booking
                    $rate  = $row['rate'] > 0 ? $row['rate'] : (float) ($tour->regular_price_per_person ?? 0);
                    $total = $rate * $row['pax'];

                    // Map terms to valid payment_method enum: xendit, cash, installment
                    $paymentMethod = $row['terms'] === 'installment' ? 'installment' : 'cash';

                    Booking::create([
                        'booking_number'    => 'DG-' . $year . '-' . str_pad(++$bookingCounter, 6, '0', STR_PAD_LEFT),
                        'tour_id'           => $tour->id,
                        'schedule_id'       => $schedule->id,
                        'tour_date'         => $row['travel_date'],
                        'adults'            => $row['pax'],
                        'children'          => 0,
                        'infants'           => 0,
                        'total_guests'      => $row['pax'],
                        'price_per_adult'   => $rate,
                        'price_per_child'   => 0,
                        'subtotal'          => $total,
                        'discount_amount'   => 0,
                        'tax_amount'        => 0,
                        'total_amount'      => $total,
                        'status'            => $row['booking_status'],
                        'payment_status'    => $row['payment_status'],
                        'payment_method'    => $paymentMethod,
                        'contact_name'      => $row['client_name'],
                        'contact_email'     => null,
                        'contact_phone'     => null,
                        'special_requests'  => $row['notes'] ?: null,
                        'downpayment_amount'=> null,
                    ]);

                    // 4. Sync booked_seats (BookingObserver only fires on update, not create)
                    if ($row['booking_status'] === 'confirmed') {
                        $schedule->increment('booked_seats', $row['pax']);
                        $schedCache[$schedKey] = $schedule->fresh();
                        if ($schedCache[$schedKey]->booked_seats >= $schedCache[$schedKey]->available_seats) {
                            $schedCache[$schedKey]->update(['status' => 'sold_out']);
                        }
                    }

                    $created++;
                } catch (\Throwable $e) {
                    Log::error('BookingImport row ' . $row['row'] . ' failed', [
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

    // =========================================================================
    // CSV Parser
    // =========================================================================

    /**
     * Read an XLSX/XLS file into a plain array of rows (each row is an array of cell values).
     */
    private function readSpreadsheetRows(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = [];
        foreach ($sheet->toArray(null, true, true, false) as $row) {
            $rows[] = array_map(fn($v) => $v !== null ? (string) $v : '', $row);
        }
        return $rows;
    }

    /**
     * Parse slot-tracker CSV/XLSX into blocks, one per route+date section.
     *
     * Each block:
     *   route_name      string   (BUS suffix stripped for consistency)
     *   travel_date_raw string   "FEB 11 - 21, 2026"
     *   total_seats     int      from "Total Seats" metadata row
     *   clients         array    each: client_name, pax, status, terms, rate, pay1_date, pay2_notes
     *
     * @param string     $path CSV file path (used when $xlsxRows is null)
     * @param array|null $xlsxRows Pre-read rows from XLSX (skips CSV reading when provided)
     */
    private function parseSlotTrackerCsv(string $path, ?array $xlsxRows = null): array
    {
        // Build row iterator — either from pre-read xlsx rows or from CSV file
        if ($xlsxRows !== null) {
            $rowIterator = $xlsxRows;
        } else {
            $handle = fopen($path, 'r');
            if (!$handle) return [];
            $rowIterator = [];
            while (($cols = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                $rowIterator[] = $cols;
            }
            fclose($handle);
        }

        $blocks  = [];
        $current = null;

        $metaKeys = ['total seats', 'occupied slots', 'available slots', 'status', 'route name'];
        $monthRx  = 'jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec'
                  . '|january|february|march|april|june|july|august'
                  . '|september|october|november|december';

        foreach ($rowIterator as $cols) {
            $cols = array_pad($cols, 10, '');
            $c1   = trim($cols[1] ?? '');
            $c2   = trim($cols[2] ?? '');
            $c3   = trim($cols[3] ?? '');

            if (trim(implode('', $cols)) === '') continue;

            // Month-section headers: "FEB 2026"
            if (preg_match('/^(' . $monthRx . ')\w*\s+\d{4}$/i', $c1)) continue;

            // Column-header rows
            if (strtolower($c1) === 'route name') continue;

            // Route block header: c1 has tour name, c2 has a date range, c3 empty or BUS label
            $isDateRange  = (bool) (preg_match('/\b(' . $monthRx . ')\b/i', $c2)
                                 && preg_match('/\d{4}/', $c2));
            $c3IsBusLabel = (bool) preg_match('/^bus\s*[\d\/]+$/i', $c3);
            $c1IsNotMeta  = $c1 !== '' && !in_array(strtolower($c1), $metaKeys);

            if ($c1IsNotMeta && $isDateRange && ($c3 === '' || $c3IsBusLabel)) {
                if ($current !== null) $blocks[] = $current;
                // Strip BUS suffix so BUS 1 / BUS 2 variants all map to the same tour
                $baseRoute = preg_replace('/\s*(BUS\s*[\d\/]+|\(BUS\s*[\d]+\))\s*[-–\s]*/i', '', $c1);
                $baseRoute = preg_replace('/\s+/', ' ', trim($baseRoute));
                $current   = [
                    'route_name'      => $baseRoute,
                    'travel_date_raw' => $c2,
                    'total_seats'     => 0,
                    'clients'         => [],
                ];
                continue;
            }

            if ($current === null) continue;

            // Metadata: capture Total Seats
            if (strtolower($c1) === 'total seats') {
                $current['total_seats'] = (int) preg_replace('/\D/', '', $c2);
                // Fall through — there may also be a client on this same line (seen in CSV)
            }

            // Client row: col[3] has a real name
            if ($c3 !== '') {
                $c3Lower = strtolower($c3);
                if (in_array($c3Lower, ['names of clients', 'route name', 'travel date'])) continue;
                if (preg_match('/^bus\s*[\d\/]+$/i', $c3)) continue;

                $current['clients'][] = [
                    'client_name' => $c3,
                    'pax'         => trim($cols[4] ?? ''),
                    'status'      => trim($cols[5] ?? ''),
                    'terms'       => trim($cols[6] ?? ''),
                    'rate'        => trim($cols[7] ?? ''),
                    'pay1_date'   => trim($cols[8] ?? ''),
                    'pay2_notes'  => trim($cols[9] ?? ''),
                ];
            }
        }

        if ($current !== null) $blocks[] = $current;

        return array_values(array_filter($blocks, fn($b) => count($b['clients']) > 0));
    }

    /**
     * Parse start or end date from a date-range string.
     * "FEB 11 - 21, 2026"       → start: Feb 11   end: Feb 21
     * "MAR 18 - APRIL 2, 2026"  → start: Mar 18   end: Apr 2
     * "APR 01-16, 2026"         → start: Apr 01   end: Apr 16
     */
    private function parseDateRange(string $range, string $which = 'start'): ?Carbon
    {
        $range = trim($range);
        if ($range === '') return null;

        $monthRx = 'jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec'
                 . '|january|february|march|april|june|july|august'
                 . '|september|october|november|december';

        if (!preg_match('/\b(\d{4})\b/', $range, $ym)) return null;
        $year = $ym[1];

        preg_match_all('/\b(' . $monthRx . ')\s+(\d{1,2})\b/i', $range, $all, PREG_SET_ORDER);

        if ($which === 'start') {
            if (empty($all)) return null;
            $m = $all[0][1];
            $d = $all[0][2];
        } else {
            if (count($all) >= 2) {
                $last = end($all);
                $m    = $last[1];
                $d    = $last[2];
            } elseif (count($all) === 1) {
                // "FEB 11 - 21": end day is bare number after dash
                $startMonth = $all[0][1];
                $afterFirst = substr($range, strpos(strtolower($range), strtolower($startMonth)) + strlen($startMonth));
                if (preg_match('/[-–]\s*(\d{1,2})\b/', $afterFirst, $dm)) {
                    $m = $startMonth;
                    $d = $dm[1];
                } else {
                    $m = $all[0][1];
                    $d = $all[0][2];
                }
            } else {
                return null;
            }
        }

        try {
            return Carbon::parse($m . ' ' . $d . ' ' . $year);
        } catch (\Throwable) {
            return null;
        }
    }

    // =========================================================================
    // Tour helpers
    // =========================================================================

    private function resolveTour(string $normKey, \Illuminate\Support\Collection $tours): ?Tour
    {
        if ($t = $tours->get($normKey)) return $t;
        $t = $tours->first(fn($t) =>
            str_contains($this->normalizeRouteName($t->title), $normKey)
            || str_contains($normKey, $this->normalizeRouteName($t->title))
        );
        if ($t) return $t;
        $bestDist = 4;
        $bestTour = null;
        foreach ($tours as $key => $t) {
            $d = levenshtein($normKey, $key);
            if ($d < $bestDist) { $bestDist = $d; $bestTour = $t; }
        }
        return $bestTour;
    }

    private function normalizeRouteName(string $name): string
    {
        $name = preg_replace('/\s*(BUS\s*[\d\/]+|\(BUS\s*[\d]+\))\s*[-–\s]*/i', '', $name);
        return strtolower(preg_replace('/\s+/', ' ', trim($name)));
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i    = 1;
        while (Tour::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function toTitleCase(string $name): string
    {
        $name = preg_replace('/\s*(BUS\s*[\d\/]+|\(BUS\s*[\d]+\))\s*[-–\s]*/i', '', $name);
        return Str::title(strtolower(trim($name)));
    }

    // =========================================================================
    // Status helpers
    // =========================================================================

    private function normalizeTerms(string $raw): string
    {
        $lower = strtolower(trim($raw));
        if (str_contains($lower, 'install'))     return 'installment';
        if (str_contains($lower, 'down'))        return 'downpayment';
        if (str_contains($lower, 'travel fund')) return 'downpayment';
        if (str_contains($lower, 'foc'))         return 'cash';
        return 'cash';
    }

    private function normalizeBookingStatus(string $csvStatus): string
    {
        $s = strtolower(trim($csvStatus));
        return ($s === 'paid' || $s === 'booking confirmation') ? 'confirmed' : 'pending';
    }

    private function derivePaymentStatus(string $csvStatus, string $normalizedTerms): string
    {
        $s = strtolower(trim($csvStatus));
        if ($s === 'paid') {
            return $normalizedTerms === 'cash' ? 'paid' : 'partial';
        }
        if (str_contains($s, 'travel fund'))          return 'partial';
        if (str_contains($s, 'booking confirmation')) return 'partial';
        return 'unpaid';
    }

    private function csvCell(string $value): string
    {
        return '"' . str_replace('"', '""', $value) . '"';
    }
}
