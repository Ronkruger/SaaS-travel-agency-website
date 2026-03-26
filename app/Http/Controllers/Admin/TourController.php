<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class TourController extends Controller
{
    public function __construct()
    {
        $this->middleware('secure.resource:tour');
    }

    // ── Index ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorize('viewAny', Tour::class);
        
        $query = Tour::withTrashed();

        if ($search = $request->input('search')) {
            // Sanitize search input
            $search = strip_tags($search);
            $query->where('title', 'like', "%{$search}%");
        }
        if ($status = $request->input('status')) {
            // Validate status against allowed values
            if (in_array($status, ['trashed', 'active', 'inactive'])) {
                if ($status === 'trashed') {
                    $query->onlyTrashed();
                } elseif ($status === 'active') {
                    $query->where('is_active', true)->withoutTrashed();
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false)->withoutTrashed();
                }
            }
        }
        if ($continent = $request->input('continent')) {
            $allowedContinents = ['Asia', 'Europe', 'Africa', 'North America', 'South America', 'Oceania', 'Antarctica'];
            if (in_array($continent, $allowedContinents)) {
                $query->where('continent', $continent);
            }
        }

        $tours = $query->latest()->paginate(15)->withQueryString();
        return view('admin.tours.index', compact('tours'));
    }

    // ── Create / Store ─────────────────────────────────────────────────────

    public function create()
    {
        $this->authorize('create', Tour::class);
        return view('admin.tours.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Tour::class);
        
        $data = $this->validateAndPrepare($request);
        $data['slug'] = $this->uniqueSlug($request, null);
        $data = $this->handleFileUploads($request, $data, null);

        Tour::create($data);
        return redirect()->route('admin.tours.index')->with('success', 'Tour created successfully.');
    }

    // ── Edit / Update ──────────────────────────────────────────────────────

    public function edit(Tour $tour)
    {
        $this->authorize('update', $tour);
        return view('admin.tours.edit', compact('tour'));
    }

    public function update(Request $request, Tour $tour)
    {
        $this->authorize('update', $tour);
        
        $data = $this->validateAndPrepare($request, $tour->id);
        $data['slug'] = $this->uniqueSlug($request, $tour);
        $data = $this->handleFileUploads($request, $data, $tour);

        $tour->update($data);
        return redirect()->route('admin.tours.index')->with('success', 'Tour updated successfully.');
    }

    // ── Destroy / Restore ──────────────────────────────────────────────────

    public function destroy(Tour $tour)
    {
        $this->authorize('delete', $tour);
        $tour->delete();
        return back()->with('success', 'Tour moved to trash.');
    }

    public function restore(int $id)
    {
        $tour = Tour::withTrashed()->findOrFail($id);
        $this->authorize('restore', $tour);
        $tour->restore();
        return back()->with('success', 'Tour restored.');
    }

    // ── Private Helpers ────────────────────────────────────────────────────

    private function validateAndPrepare(Request $request, ?int $ignoreId = null): array
    {
        $request->validate([
            'title'                        => ['required', 'string', 'max:255'],
            'duration_days'                => ['required', 'integer', 'min:1'],
            'regular_price_per_person'     => ['nullable', 'numeric', 'min:0'],
            'promo_price_per_person'       => ['nullable', 'numeric', 'min:0'],
            'base_price_per_day'           => ['nullable', 'numeric', 'min:0'],
            'main_image'                   => [$ignoreId ? 'nullable' : 'nullable', 'image', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
            'gallery_image_files.*'        => ['nullable', 'image', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
            'related_image_files.*'        => ['nullable', 'image', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
            // Enhanced video validation - limit file types and add extension check
            'video_file'                   => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm', 'mimes:mp4,mov,webm', 'max:102400'],
        ]);

        return [
            // Basic info
            'title'                         => $request->input('title'),
            'summary'                       => $request->input('summary'),
            'short_description'             => $request->input('short_description'),
            'line'                          => $request->input('line'),
            'continent'                     => $request->input('continent'),
            'duration_days'                 => (int) $request->input('duration_days', 1),
            'guaranteed_departure'          => $request->boolean('guaranteed_departure'),
            'booking_pdf_url'               => $request->input('booking_pdf_url'),
            'video_url'                     => $request->input('video_url'),
            'facebook_post_url'             => $request->input('facebook_post_url'),
            // Pricing
            'regular_price_per_person'      => $request->input('regular_price_per_person') ?: null,
            'promo_price_per_person'        => $request->input('promo_price_per_person') ?: null,
            'base_price_per_day'            => $request->input('base_price_per_day') ?: null,
            'is_sale_enabled'               => $request->boolean('is_sale_enabled'),
            'sale_end_date'                 => $request->input('sale_end_date') ?: null,
            // Travel window
            'travel_window'                 => array_filter([
                'start' => $request->input('travel_window_start'),
                'end'   => $request->input('travel_window_end'),
            ]) ?: null,
            // JSON content
            'highlights'                    => $this->parseLines($request->input('highlights')),
            'departure_dates'               => $this->parseDepartureDates($request->input('departure_dates', [])),
            'itinerary'                     => $this->parseItinerary($request->input('itinerary', [])),
            'full_stops'                    => $this->parseFullStops($request->input('full_stops', [])),
            'additional_info'               => $this->parseAdditionalInfo($request),
            'booking_links'                 => $this->parseBookingLinks($request->input('booking_links', [])),
            'optional_tours'                => $this->parseOptionalTours($request->input('optional_tours', [])),
            'cash_freebies'                 => $this->parseCashFreebies($request->input('cash_freebies', [])),
            // Booking
            'allows_downpayment'            => $request->boolean('allows_downpayment'),
            'fixed_downpayment_amount'      => $request->input('fixed_downpayment_amount') ?: null,
            'balance_due_days_before_travel'=> $request->input('balance_due_days_before_travel') ?: null,
            // Status
            'is_active'                     => $request->boolean('is_active'),
            'is_featured'                   => $request->boolean('is_featured'),
        ];
    }

    private function uniqueSlug(Request $request, ?Tour $tour): string
    {
        $base = $request->filled('slug')
            ? Str::slug($request->input('slug'))
            : Str::slug($request->input('title'));

        if ($tour && $base === $tour->slug) {
            return $tour->slug;
        }

        $slug  = $base;
        $count = 0;
        while (Tour::where('slug', $slug)->when($tour, fn($q) => $q->where('id', '!=', $tour->id))->exists()) {
            $slug = $base . '-' . ++$count;
        }
        return $slug;
    }

    private function handleFileUploads(Request $request, array $data, ?Tour $existing): array
    {
        if ($request->hasFile('main_image')) {
            $this->cloudinaryDelete($existing?->main_image);
            $data['main_image'] = $request->file('main_image')
                ->storeOnCloudinary('tours/main')
                ->getSecurePath();
        }

        if ($request->hasFile('video_file')) {
            $this->cloudinaryDelete($existing?->video_file, 'video');
            $data['video_file'] = $request->file('video_file')
                ->storeOnCloudinary('tours/videos')
                ->getSecurePath();
        }

        if ($request->hasFile('gallery_image_files')) {
            $gallery = [];
            foreach ($request->file('gallery_image_files') as $file) {
                $gallery[] = $file->storeOnCloudinary('tours/gallery')->getSecurePath();
            }
            $data['gallery_images'] = $gallery;
        }

        if ($request->hasFile('related_image_files')) {
            $related = [];
            foreach ($request->file('related_image_files') as $file) {
                $related[] = $file->storeOnCloudinary('tours/related')->getSecurePath();
            }
            $data['related_images'] = $related;
        }

        return $data;
    }

    /**
     * Delete a file from Cloudinary (full URL) or the legacy public disk (relative path).
     */
    private function cloudinaryDelete(?string $path, string $resourceType = 'image'): void
    {
        if (!$path) return;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            // Extract Cloudinary public_id: path after /upload/v{version}/ without extension
            $urlPath = parse_url($path, PHP_URL_PATH);
            $publicId = preg_replace('#^.*/upload/(?:v\d+/)?#', '', $urlPath);
            $publicId = preg_replace('#\.[^.]+$#', '', $publicId);
            Cloudinary::destroy($publicId, ['resource_type' => $resourceType]);
        } else {
            Storage::disk('public')->delete($path);
        }
    }

    private function parseLines(?string $input): array
    {
        if (!$input) return [];
        return array_values(array_filter(array_map('trim', explode("\n", $input))));
    }

    private function parseDepartureDates(array $input): array
    {
        $dates = [];
        foreach ($input as $row) {
            if (empty($row['start'])) continue;
            $dates[] = [
                'start'           => $row['start'],
                'end'             => $row['end'] ?? null,
                'price'           => isset($row['price']) && $row['price'] !== '' ? (float) $row['price'] : null,
                'maxCapacity'     => isset($row['maxCapacity']) && $row['maxCapacity'] !== '' ? (int) $row['maxCapacity'] : null,
                'currentBookings' => (int) ($row['currentBookings'] ?? 0),
                'isAvailable'     => isset($row['isAvailable']) && $row['isAvailable'] == '1',
            ];
        }
        return $dates;
    }

    private function parseItinerary(array $input): array
    {
        $itinerary = [];
        foreach ($input as $i => $day) {
            if (empty($day['title'])) continue;
            $itinerary[] = [
                'day'         => $i + 1,
                'title'       => $day['title'],
                'description' => $day['description'] ?? '',
                'image'       => $day['image'] ?? null,
            ];
        }
        return $itinerary;
    }

    private function parseFullStops(array $input): array
    {
        $stops = [];
        foreach ($input as $stop) {
            if (empty($stop['city'])) continue;
            $stops[] = [
                'city'    => trim($stop['city']),
                'country' => trim($stop['country'] ?? ''),
                'days'    => isset($stop['days']) && $stop['days'] !== '' ? (int) $stop['days'] : null,
            ];
        }
        return $stops;
    }

    private function parseAdditionalInfo(Request $request): array
    {
        $ai = [];

        if ($request->filled('ai_starting_point')) {
            $ai['starting_point'] = $request->input('ai_starting_point');
        }
        if ($request->filled('ai_ending_point')) {
            $ai['ending_point'] = $request->input('ai_ending_point');
        }
        if ($request->filled('ai_countries_visited')) {
            $ai['countries_visited'] = $this->parseLines($request->input('ai_countries_visited'));
        }

        $mainCities = [];
        foreach ($request->input('ai_main_cities', []) as $row) {
            if (empty($row['country'])) continue;
            $mainCities[] = [
                'country' => $row['country'],
                'cities'  => array_filter(array_map('trim', explode(',', $row['cities_text'] ?? ''))),
            ];
        }
        if ($mainCities) $ai['main_cities'] = $mainCities;

        $countries = $this->parseNameImagePairs($request->input('ai_countries', []));
        if ($countries) $ai['countries'] = $countries;

        $cities = $this->parseNameImagePairs($request->input('ai_cities_to_visit', []));
        if ($cities) $ai['cities_to_visit'] = $cities;

        return $ai ?: [];
    }

    private function parseNameImagePairs(array $input): array
    {
        $pairs = [];
        foreach ($input as $row) {
            if (empty($row['name'])) continue;
            $pairs[] = ['name' => $row['name'], 'image' => $row['image'] ?? null];
        }
        return $pairs;
    }

    private function parseBookingLinks(array $input): array
    {
        $links = [];
        foreach ($input as $group) {
            if (empty($group['year'])) continue;
            $urls = array_values(array_filter(array_map('trim', (array) ($group['urls'] ?? []))));
            if ($urls) {
                $links[] = ['year' => (int) $group['year'], 'urls' => $urls];
            }
        }
        return $links;
    }

    private function parseOptionalTours(array $input): array
    {
        $tours = [];
        foreach ($input as $row) {
            if (empty($row['title'])) continue;
            $tours[] = [
                'day'          => isset($row['day']) && $row['day'] !== '' ? (int) $row['day'] : null,
                'title'        => $row['title'],
                'regularPrice' => isset($row['regularPrice']) && $row['regularPrice'] !== '' ? (float) $row['regularPrice'] : null,
                'promoType'    => $row['promoType'] ?? null,
                'promoValue'   => isset($row['promoValue']) && $row['promoValue'] !== '' ? (float) $row['promoValue'] : null,
                'flipbookUrl'  => $row['flipbookUrl'] ?? null,
            ];
        }
        return $tours;
    }

    private function parseCashFreebies(array $input): array
    {
        $freebies = [];
        foreach ($input as $row) {
            if (empty($row['label'])) continue;
            $freebies[] = [
                'label' => $row['label'],
                'type'  => $row['type'] ?? 'cash',
                'value' => isset($row['value']) && $row['value'] !== '' ? (float) $row['value'] : null,
            ];
        }
        return $freebies;
    }
}
