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
        $example1 = [
            'Route K Deluxe', 'FEB 11 - 21, 2026', 'Juan dela Cruz', '2',
            'Paid', 'Full Cash', '₱180,000.00', 'Apr 1, 2026', 'Confirmed departure',
        ];
        $example2 = [
            '', '', 'Maria Santos (rebooked from MAY)', '1',
            'Paid', 'Downpayment', '₱150,000.00', 'Jan 15, 2026', '',
        ];
        $example3 = [
            '', '', 'Pedro Reyes', '2',
            'Paid', 'FOC', '', '', 'Free of Charge guest',
        ];
        $csv  = implode(',', array_map([$this, 'csvCell'], $headers)) . "\r\n";
        $csv .= implode(',', array_map([$this, 'csvCell'], $example1)) . "\r\n";
        $csv .= implode(',', array_map([$this, 'csvCell'], $example2)) . "\r\n";
        $csv .= implode(',', array_map([$this, 'csvCell'], $example3)) . "\r\n";

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
            'csv_file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('csv_file');
        $ext  = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, ['csv', 'txt', 'xlsx', 'xls'])) {
            return back()->withErrors(['csv_file' => 'Invalid file type. Please upload a CSV, XLSX, or XLS file.']);
        }

        // Save the uploaded file to storage so we can re-read it during confirm
        $storedPath = $file->store('imports', 'local');

        $fullPath = storage_path('app/' . $storedPath);

        try {
            $rows = in_array($ext, ['xlsx', 'xls'])
                ? $this->readSpreadsheetRows($fullPath)
                : null;
        } catch (\Throwable $e) {
            Log::error('XLSX read failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['csv_file' => 'Failed to read spreadsheet: ' . Str::limit($e->getMessage(), 150)]);
        }

        $blocks = $this->parseSlotTrackerCsv($fullPath, $rows);

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

            // Determine block-level rate: most common non-zero rate among clients
            $blockRate = $this->resolveBlockRate($block['clients']);

            foreach ($block['clients'] as $client) {
                $rowNum++;
                $pax         = max(1, (int) ($client['pax'] ?: 1));
                $terms       = $this->normalizeTerms($client['terms']);
                $isFoc       = ($terms === 'foc');
                $clientRate  = $this->parseRate($client['rate'] ?? '');
                $colJNotes   = trim($client['pay2_notes'] ?? '');

                // FOC (Free of Charge) bookings are complimentary — always ₱0
                if ($isFoc) {
                    $rate  = 0;
                    $total = 0;
                } else {
                    // Fall back to block rate, then tour price
                    $rate  = $clientRate > 0
                        ? $clientRate
                        : ($blockRate > 0 ? $blockRate : (float) ($tour?->regular_price_per_person ?? 0));
                    $total = $rate > 0 ? $rate * $pax : 0;
                }

                $csvStatus     = trim($client['status'] ?? '');
                $bookingStatus = $this->normalizeBookingStatus($csvStatus, $colJNotes);
                $paymentStatus = $isFoc ? 'paid' : $this->derivePaymentStatus($csvStatus, $terms, $colJNotes);

                // Parse all parenthetical annotations from client name in a single pass
                [$rawName, $annotations] = $this->parseClientName($client['client_name']);

                // Identify "rebooked from ..." and other notes from annotations
                $rebooked   = null;
                $clientNote = null;
                foreach ($annotations as $ann) {
                    if (preg_match('/^rebooked\s+from\s+(.+)$/i', $ann, $rbm)) {
                        $rebooked = trim($rbm[1]);
                    } else {
                        $clientNote = ($clientNote ? $clientNote . ' | ' : '') . $ann;
                    }
                }

                // Build combined notes
                $noteParts = array_filter([
                    $rebooked   ? 'Rebooked from ' . $rebooked : null,
                    $isFoc      ? 'FOC (Free of Charge)' : null,
                    $clientNote ?: null,
                ]);
                $combinedNotes = implode(' | ', $noteParts) ?: null;

                $rowWarnings = [];
                if ($willCreateTour) {
                    $rowWarnings[] = "Tour not found — will be auto-created as \"{$tourTitle}\".";
                }
                if (!$departure) {
                    $rowWarnings[] = 'Cannot parse travel date "' . $dateRaw . '" — row will be skipped.';
                }
                if (!$isFoc && $clientRate <= 0 && $rate > 0) {
                    $rowWarnings[] = 'No rate in CSV — using ₱' . number_format($rate, 0) . ' from tour/block.';
                } elseif (!$isFoc && $rate <= 0) {
                    $rowWarnings[] = 'No rate available — will store as ₱0.';
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
                    'client_name'      => $rawName,
                    'pax'              => $pax,
                    'booking_status'   => $bookingStatus,
                    'payment_status'   => $paymentStatus,
                    'terms'            => $terms,
                    'terms_raw'        => trim($client['terms']),
                    'rate'             => $rate,
                    'total_amount'     => $total,
                    'pay1_date'        => $client['pay1_date'],
                    'pay2_status'      => trim($client['pay2_notes'] ?? '') ?: null,
                    'notes'            => $combinedNotes,
                    'rebooked_from'    => $rebooked,
                    'is_foc'           => $isFoc,
                    'skipped'          => !$departure,
                    'warnings'         => $rowWarnings,
                ];

                $realWarnings = array_filter($rowWarnings, fn($w) => !str_contains($w, 'auto-created') && !str_contains($w, 'from tour/block'));
                if ($realWarnings) {
                    $warnings[] = "Row {$rowNum}: " . implode(' | ', $realWarnings);
                }
            }
        }

        $importable = collect($preview)->where('skipped', false)->count();

        // Store file path + extension in session (small data), NOT the full preview array
        session([
            'import_file_path' => $storedPath,
            'import_file_ext'  => $ext,
        ]);

        return view('admin.import.index', compact('preview', 'warnings', 'importable'));
    }

    /* -----------------------------------------------------------------------
     | POST /admin/import/confirm
     * --------------------------------------------------------------------- */
    public function confirm(Request $request)
    {
        $storedPath = session('import_file_path');
        $ext        = session('import_file_ext', 'csv');

        if (!$storedPath || !file_exists(storage_path('app/' . $storedPath))) {
            return redirect()->route('admin.import.index')
                ->withErrors(['csv_file' => 'No import data found. Please upload the file again.']);
        }

        $fullPath = storage_path('app/' . $storedPath);

        // Re-parse the file from disk (instead of reading from session)
        try {
            $rows = in_array($ext, ['xlsx', 'xls'])
                ? $this->readSpreadsheetRows($fullPath)
                : null;
        } catch (\Throwable $e) {
            Log::error('XLSX re-read failed', ['error' => $e->getMessage()]);
            return redirect()->route('admin.import.index')
                ->withErrors(['csv_file' => 'Failed to re-read spreadsheet: ' . Str::limit($e->getMessage(), 150)]);
        }

        $blocks = $this->parseSlotTrackerCsv($fullPath, $rows);

        if (empty($blocks)) {
            return redirect()->route('admin.import.index')
                ->withErrors(['csv_file' => 'Could not re-parse the uploaded file.']);
        }

        // Build preview rows from blocks (same logic as preview, but simplified)
        $existingTours = Tour::select('id', 'title', 'regular_price_per_person')
            ->get()
            ->keyBy(fn($t) => $this->normalizeRouteName($t->title));

        $preview       = [];
        $scheduleSeats = [];

        foreach ($blocks as $block) {
            $rawRoute   = $block['route_name'];
            $matchKey   = $this->normalizeRouteName($rawRoute);
            $dateRaw    = $block['travel_date_raw'];
            $totalSeats = $block['total_seats'];
            $departure  = $this->parseDateRange($dateRaw, 'start');
            $returnDate = $this->parseDateRange($dateRaw, 'end');
            $tour       = $this->resolveTour($matchKey, $existingTours);
            $tourTitle  = $tour?->title ?? $this->toTitleCase($rawRoute);
            $blockRate  = $this->resolveBlockRate($block['clients']);

            if (!$departure) continue; // skip unparseable date blocks

            $schedKey = $matchKey . '|' . $departure->format('Y-m-d');
            if (!isset($scheduleSeats[$schedKey])) {
                $scheduleSeats[$schedKey] = $totalSeats > 0 ? $totalSeats : 40;
            }

            foreach ($block['clients'] as $client) {
                $pax        = max(1, (int) ($client['pax'] ?: 1));
                $terms      = $this->normalizeTerms($client['terms']);
                $isFoc      = ($terms === 'foc');
                $clientRate = $this->parseRate($client['rate'] ?? '');
                $colJNotes  = trim($client['pay2_notes'] ?? '');

                if ($isFoc) {
                    $rate  = 0;
                    $total = 0;
                } else {
                    $rate  = $clientRate > 0
                        ? $clientRate
                        : ($blockRate > 0 ? $blockRate : (float) ($tour?->regular_price_per_person ?? 0));
                    $total = $rate * $pax;
                }

                $csvStatus     = trim($client['status'] ?? '');
                $bookingStatus = $this->normalizeBookingStatus($csvStatus, $colJNotes);
                $paymentStatus = $isFoc ? 'paid' : $this->derivePaymentStatus($csvStatus, $terms, $colJNotes);

                // Parse annotations from client name in a single pass
                [$rawName, $annotations] = $this->parseClientName($client['client_name']);
                $rebooked   = null;
                $clientNote = null;
                foreach ($annotations as $ann) {
                    if (preg_match('/^rebooked\s+from\s+(.+)$/i', $ann, $rbm)) {
                        $rebooked = trim($rbm[1]);
                    } else {
                        $clientNote = ($clientNote ? $clientNote . ' | ' : '') . $ann;
                    }
                }

                $noteParts = array_filter([
                    $rebooked   ? 'Rebooked from ' . $rebooked : null,
                    $isFoc      ? 'FOC (Free of Charge)' : null,
                    $clientNote ?: null,
                ]);
                $preview[] = [
                    'tour_name'      => $tourTitle,
                    'travel_date'    => $departure->format('Y-m-d'),
                    'return_date'    => ($returnDate ?? $departure)->format('Y-m-d'),
                    'total_seats'    => $totalSeats,
                    'client_name'    => $rawName,
                    'pax'            => $pax,
                    'booking_status' => $bookingStatus,
                    'payment_status' => $paymentStatus,
                    'terms'          => $terms,
                    'rate'           => $rate,
                    'total_amount'   => $total,
                    'pay1_date'      => $client['pay1_date'],
                    'pay2_status'    => trim($client['pay2_notes'] ?? '') ?: null,
                    'notes'          => implode(' | ', $noteParts) ?: null,
                    'is_foc'         => $isFoc,
                ];
            }
        }

        if (empty($preview)) {
            return redirect()->route('admin.import.index')
                ->withErrors(['csv_file' => 'No importable rows found in file.']);
        }

        $tourCache  = $existingTours;
        $schedCache = [];
        $created    = $skipped = $updated = 0;
        $errors     = [];

        $year           = date('Y');
        $prefix         = 'DG-' . $year . '-';
        $lastNumber     = Booking::withTrashed()
            ->where('booking_number', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(booking_number, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->value('booking_number');
        $bookingCounter = $lastNumber
            ? (int) substr($lastNumber, strlen($prefix))
            : 0;

        DB::transaction(function () use ($preview, $scheduleSeats, &$tourCache, &$schedCache, &$created, &$skipped, &$updated, &$errors, $year, &$bookingCounter) {
            foreach ($preview as $idx => $row) {
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
                        if (!$schedule->wasRecentlyCreated && $availSeats > 0
                            && $schedule->available_seats !== $availSeats) {
                            $schedule->update(['available_seats' => $availSeats]);
                        }
                        $schedCache[$schedKey] = $schedule;
                    }
                    $schedule = $schedCache[$schedKey];

                    // 3. Create Booking
                    $isFoc = $row['is_foc'] ?? false;
                    if ($isFoc) {
                        $rate  = 0;
                        $total = 0;
                    } else {
                        $rate  = $row['rate'] > 0 ? $row['rate'] : (float) ($tour->regular_price_per_person ?? 0);
                        $total = $rate * $row['pax'];
                    }

                    $isInstallment = !$isFoc && in_array($row['terms'], ['installment', 'downpayment']);
                    $paymentMethod = $isFoc ? 'cash' : ($isInstallment ? 'installment' : 'cash');

                    $downpaymentAmount   = null;
                    $installmentMonths   = null;
                    $installmentSchedule = null;

                    if ($isInstallment && $total > 0) {
                        $tourDate     = Carbon::parse($row['travel_date']);
                        $firstPayDate = $this->parseLooseDate($row['pay1_date'] ?? '');
                        $bookingDate  = $firstPayDate ?? now();

                        $monthsUntilTour   = max(1, (int) $bookingDate->diffInMonths($tourDate));
                        $installmentMonths = min($monthsUntilTour, 12);

                        $downpaymentAmount = $rate;
                        $remaining         = max(0, $total - $downpaymentAmount);
                        $monthlyAmount     = $installmentMonths > 1
                            ? (float) ceil($remaining / ($installmentMonths - 1))
                            : $remaining;

                        $payTerms = [];
                        $dpPaid   = in_array($row['payment_status'], ['paid', 'partial']);
                        $payTerms[] = [
                            'type'     => 'downpayment',
                            'term'     => 0,
                            'due_date' => ($firstPayDate ?? $bookingDate)->toDateString(),
                            'amount'   => (float) $downpaymentAmount,
                            'status'   => $dpPaid ? 'paid' : 'pending',
                            'paid_at'  => $dpPaid ? ($firstPayDate ?? $bookingDate)->toDateString() : null,
                        ];

                        $termCount = max(1, $installmentMonths - 1);
                        if ($remaining > 0) {
                            for ($i = 1; $i <= $termCount; $i++) {
                                $dueDate = $bookingDate->copy()->addMonths($i);
                                if ($dueDate->gt($tourDate)) {
                                    $dueDate = $tourDate->copy()->subDays(7);
                                }
                                $amt = ($i === $termCount) ? max(0, $remaining - ($monthlyAmount * ($termCount - 1))) : $monthlyAmount;
                                $payTerms[] = [
                                    'type'     => 'installment',
                                    'term'     => $i,
                                    'due_date' => $dueDate->toDateString(),
                                    'amount'   => (float) max(0, $amt),
                                    'status'   => 'pending',
                                ];
                            }
                        }

                        $installmentSchedule = $payTerms;
                    }

                    // Build special_requests with first payment date + notes
                    $noteFragments = array_filter([
                        $row['pay1_date'] ? '1st Payment: ' . $row['pay1_date'] : null,
                        $row['notes'] ?: null,
                    ]);

                    // Skip duplicate: same client name + tour + tour_date
                    $existingBooking = Booking::where('contact_name', $row['client_name'])
                        ->where('tour_id', $tour->id)
                        ->whereDate('tour_date', $row['travel_date'])
                        ->where('total_guests', $row['pax'])
                        ->first();

                    if ($existingBooking) {
                        // Update fields that may have changed (payment status, 2nd payment, etc.)
                        $existingBooking->update([
                            'status'                  => $row['booking_status'],
                            'payment_status'          => $isFoc ? 'paid' : $row['payment_status'],
                            'payment_method'          => $paymentMethod,
                            'total_amount'            => $total,
                            'price_per_adult'         => $rate,
                            'subtotal'                => $total,
                            'special_requests'        => implode(' | ', $noteFragments) ?: null,
                            'downpayment_amount'      => $downpaymentAmount,
                            'installment_months'      => $installmentMonths,
                            'installment_schedule'    => $installmentSchedule,
                            'second_payment_status'   => $row['pay2_status'] ?? null,
                        ]);
                        $updated++;
                        continue;
                    }

                    Booking::create([
                        'booking_number'       => 'DG-' . $year . '-' . str_pad(++$bookingCounter, 6, '0', STR_PAD_LEFT),
                        'tour_id'              => $tour->id,
                        'schedule_id'          => $schedule->id,
                        'tour_date'            => $row['travel_date'],
                        'adults'               => $row['pax'],
                        'children'             => 0,
                        'infants'              => 0,
                        'total_guests'         => $row['pax'],
                        'price_per_adult'      => $rate,
                        'price_per_child'      => 0,
                        'subtotal'             => $total,
                        'discount_amount'      => 0,
                        'tax_amount'           => 0,
                        'total_amount'         => $total,
                        'status'               => $row['booking_status'],
                        'payment_status'       => $isFoc ? 'paid' : $row['payment_status'],
                        'payment_method'       => $paymentMethod,
                        'contact_name'         => $row['client_name'],
                        'contact_email'        => null,
                        'contact_phone'        => null,
                        'special_requests'     => implode(' | ', $noteFragments) ?: null,
                        'downpayment_amount'      => $downpaymentAmount,
                        'installment_months'      => $installmentMonths,
                        'installment_schedule'    => $installmentSchedule,
                        'second_payment_status'   => $row['pay2_status'] ?? null,
                    ]);

                    if ($row['booking_status'] === 'confirmed') {
                        $schedule->increment('booked_seats', $row['pax']);
                        $schedCache[$schedKey] = $schedule->fresh();
                        if ($schedCache[$schedKey]->booked_seats >= $schedCache[$schedKey]->available_seats) {
                            $schedCache[$schedKey]->update(['status' => 'sold_out']);
                        }
                    }

                    $created++;
                } catch (\Throwable $e) {
                    Log::error('BookingImport row ' . ($idx + 1) . ' failed', [
                        'error' => $e->getMessage(),
                        'client' => $row['client_name'] ?? 'unknown',
                    ]);
                    $errors[] = ($row['client_name'] ?? 'Row ' . ($idx + 1)) . ': ' . Str::limit($e->getMessage(), 120);
                    $skipped++;
                }
            }
        });

        // Cleanup stored file and session
        @unlink($fullPath);
        session()->forget(['import_file_path', 'import_file_ext']);

        return redirect()->route('admin.import.index')
            ->with('success', "Import complete — {$created} created, {$updated} updated, {$skipped} skipped.")
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
        // Step 1: Peek at sheet names WITHOUT loading cell data (fast, low memory)
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        $sheetNames    = $reader->listWorksheetNames($path);
        $targetSheet   = null;
        $year          = date('Y');

        // Prefer "DETAILED SLOTS TRACKER 2026"
        foreach ($sheetNames as $name) {
            if (stripos($name, 'detailed slots tracker') !== false
                && str_contains($name, (string) $year)) {
                $targetSheet = $name;
                break;
            }
        }
        // Fall back: any sheet with "slots tracker"
        if (!$targetSheet) {
            foreach ($sheetNames as $name) {
                if (stripos($name, 'slots tracker') !== false) {
                    $targetSheet = $name;
                    break;
                }
            }
        }
        // Last resort: first sheet
        if (!$targetSheet) {
            $targetSheet = $sheetNames[0] ?? null;
        }

        // Step 2: Load ONLY the target sheet (saves memory on large multi-sheet files)
        if ($targetSheet) {
            $reader->setLoadSheetsOnly([$targetSheet]);
        }

        $spreadsheet = $reader->load($path);
        $sheet       = $spreadsheet->getActiveSheet();

        $rows = [];
        foreach ($sheet->toArray(null, true, true, false) as $row) {
            $rows[] = array_map(fn($v) => $v !== null ? (string) $v : '', $row);
        }

        // Free memory
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

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

        // Auto-detect column offset.
        // The DG SLOTS TRACKER spreadsheet has data starting at col B (index 1),
        // but template CSVs have data starting at col A (index 0).
        // Detect by scanning the first 15 rows for where "Route Name" header lives.
        $needsShift      = false;
        $skipHeaderRow   = null; // row index to skip (template CSV header)
        $dateHintRx      = '/\b(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\b.*\d{4}/i';
        foreach (array_slice($rowIterator, 0, 15) as $ri => $checkRow) {
            $checkRow = array_pad((array) $checkRow, 10, '');
            $c0 = strtolower(trim($checkRow[0] ?? ''));
            $c1 = strtolower(trim($checkRow[1] ?? ''));
            // "Route Name" at col 0 → template CSV: shift everything right by 1 and skip this row
            if ($c0 === 'route name') {
                $needsShift    = true;
                $skipHeaderRow = $ri;
                break;
            }
            // "Route Name" at col 1 → SLOTS TRACKER native format (no shift needed)
            if ($c1 === 'route name') {
                break;
            }
            // Data row heuristic: col 0 non-empty, col 1 looks like a date range → template CSV
            if ($c0 !== '' && preg_match($dateHintRx, trim($checkRow[1] ?? ''))) {
                $needsShift = true;
                break;
            }
        }
        if ($needsShift) {
            $rowIterator = array_values(array_map(fn($row) => array_merge([''], (array) $row), $rowIterator));
            // Remove the header row (now shifted, it would be parsed as a block row otherwise)
            if ($skipHeaderRow !== null) {
                array_splice($rowIterator, $skipHeaderRow, 1);
            }
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

            if ($c1IsNotMeta && $isDateRange && ($c3 === '' || $c3IsBusLabel || ctype_digit(preg_replace('/\D/', '', $c3) ?: '_'))) {
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
     * "APR 1 – 17, 2026"        → em-dash handled
     */
    private function parseDateRange(string $range, string $which = 'start'): ?Carbon
    {
        $range = trim($range);
        if ($range === '') return null;

        // Normalise em-dash / en-dash to plain hyphen
        $range = str_replace(['–', '—', '‐'], '-', $range);

        $monthRx = 'jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec'
                 . '|january|february|march|april|june|july|august'
                 . '|september|october|november|december';

        if (!preg_match('/\b(\d{4})\b/', $range, $ym)) return null;
        $year = (int) $ym[1];

        // Find all "MONTH DAY" occurrences
        preg_match_all('/\b(' . $monthRx . ')\w*\.?\s+(\d{1,2})\b/i', $range, $all, PREG_SET_ORDER);

        if ($which === 'start') {
            if (empty($all)) return null;
            try { return Carbon::createFromFormat('F j Y', ucfirst(strtolower($all[0][1])) . ' ' . $all[0][2] . ' ' . $year); }
            catch (\Throwable) { try { return Carbon::parse($all[0][1] . ' ' . $all[0][2] . ' ' . $year); } catch (\Throwable) { return null; } }
        }

        // --- end date ---
        if (count($all) >= 2) {
            // Two explicit month+day found (e.g. "MAR 18 - APR 2, 2026")
            $last = end($all);
        } elseif (count($all) === 1) {
            // "FEB 11 - 21, 2026": look for a bare day number after the first day
            $startMonth = $all[0][1];
            // Offset past the first day occurrence in the ORIGINAL string
            $offset = stripos($range, $startMonth);
            $after  = substr($range, $offset + strlen($startMonth));
            // Skip the start day + any whitespace, then look for dash followed by a day
            if (preg_match('/\s*\d{1,2}\s*-\s*(\d{1,2})\b/', $after, $dm)) {
                $last = ['month_raw' => $startMonth, 'day' => $dm[1], 1 => $startMonth, 2 => $dm[1]];
            } else {
                $last = $all[0]; // same day range
            }
        } else {
            return null;
        }

        try { return Carbon::createFromFormat('F j Y', ucfirst(strtolower($last[1])) . ' ' . $last[2] . ' ' . $year); }
        catch (\Throwable) { try { return Carbon::parse($last[1] . ' ' . $last[2] . ' ' . $year); } catch (\Throwable) { return null; } }
    }

    /**
     * Parse a loose date string like "October 15, 2025", "Jun 5, 2025", "February 2026", etc.
     */
    private function parseLooseDate(string $raw): ?Carbon
    {
        $raw = trim($raw);
        if ($raw === '') return null;
        try {
            return Carbon::parse($raw);
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

    private function resolveBlockRate(array $clients): float
    {
        $rates = [];
        foreach ($clients as $c) {
            $r = $this->parseRate($c['rate'] ?? '');
            if ($r > 0) $rates[] = (string) $r;
        }
        if (empty($rates)) return 0;
        $counts = array_count_values($rates);
        arsort($counts);
        return (float) array_key_first($counts);
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
        if ($lower === '' || $lower === 'n/a') return 'cash';
        if ($lower === 'foc' || str_contains($lower, 'free of charge')) return 'foc';
        if (str_contains($lower, 'travel fund'))   return 'travel_fund';
        if (str_contains($lower, 'install'))       return 'installment';
        if (str_contains($lower, 'down'))          return 'downpayment';
        if (str_contains($lower, 'full'))          return 'cash'; // "Full Cash", "Full Payment"
        if (str_contains($lower, 'cash'))          return 'cash';
        if (str_contains($lower, 'balance'))       return 'installment'; // balance due = final installment
        return 'cash';
    }

    private function normalizeBookingStatus(string $csvStatus, string $colJNotes = ''): string
    {
        $s    = strtolower(trim($csvStatus));
        $noteLower = strtolower(trim($colJNotes));
        if ($s === 'paid' || str_contains($s, 'booking confirmation') || str_contains($s, 'confirmed')) {
            return 'confirmed';
        }
        // Col J keywords that signal confirmed departure
        if (str_contains($noteLower, 'confirmed departure') || str_contains($noteLower, 'confirmed dep')) {
            return 'confirmed';
        }
        if ($s === 'refunded' || $s === 'refund') return 'refunded';
        if ($s === 'cancelled' || $s === 'cancel') return 'cancelled';
        return 'pending';
    }

    private function derivePaymentStatus(string $csvStatus, string $normalizedTerms, string $colJNotes = ''): string
    {
        $s         = strtolower(trim($csvStatus));
        $noteLower = strtolower(trim($colJNotes));
        if ($normalizedTerms === 'foc')         return 'paid';
        if ($normalizedTerms === 'travel_fund') return 'paid';
        if ($s === 'refunded' || $s === 'refund') return 'refunded';
        // "Fully Paid" in notes
        if (str_contains($noteLower, 'fully paid') || str_contains($noteLower, 'full payment')) {
            return 'paid';
        }
        if ($s === 'paid') {
            return in_array($normalizedTerms, ['cash']) ? 'paid' : 'partial';
        }
        if (str_contains($s, 'booking confirmation')) return 'partial';
        return 'unpaid';
    }

    private function csvCell(string $value): string
    {
        return '"' . str_replace('"', '""', $value) . '"';
    }

    /**
     * Extract numeric value from a rate string like "₱180,000.00", "180000", "180,000".
     * Handles both Philippine peso (comma thousands, dot decimal) and
     * European style (dot thousands, comma decimal).
     */
    private function parseRate(string $raw): float
    {
        $raw = trim($raw);
        if ($raw === '' || strtolower($raw) === 'foc') return 0.0;
        // Strip currency symbols, whitespace
        $raw = preg_replace('/[₱$€£\s]/u', '', $raw);
        // If there are both commas and dot: "180,000.00" → dot is decimal
        if (str_contains($raw, '.') && str_contains($raw, ',')) {
            if (strrpos($raw, '.') > strrpos($raw, ',')) {
                // "180,000.00" — comma = thousands, dot = decimal
                $raw = str_replace(',', '', $raw);
            } else {
                // "180.000,00" — dot = thousands, comma = decimal
                $raw = str_replace('.', '', $raw);
                $raw = str_replace(',', '.', $raw);
            }
        } else {
            // Only commas — treat as thousands separator
            $raw = str_replace(',', '', $raw);
        }
        return (float) $raw;
    }

    /**
     * Strip all parenthetical annotations from a client name and return
     * both the cleaned name and an array of extracted annotation strings.
     *
     * E.g. "Juan (rebooked from MAY) (Sir Godwin)" → ["Juan", ["rebooked from MAY", "Sir Godwin"]]
     */
    private function parseClientName(string $raw): array
    {
        $annotations = [];
        // Extract all (...) groups
        preg_match_all('/\(([^)]+)\)/u', $raw, $matches);
        if (!empty($matches[1])) {
            $annotations = array_map('trim', $matches[1]);
        }
        $clean = trim(preg_replace('/\s*\([^)]*\)\s*/u', ' ', $raw));
        $clean = preg_replace('/\s+/', ' ', $clean);
        return [$clean, $annotations];
    }
}
