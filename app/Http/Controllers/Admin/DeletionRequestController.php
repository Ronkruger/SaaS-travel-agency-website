<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\DeletionRequest;
use App\Models\TourSchedule;
use Illuminate\Http\Request;

class DeletionRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    /**
     * List deletion requests (super_admin sees all; staff sees own).
     */
    public function index(Request $request)
    {
        $user  = auth('admin')->user();
        $query = DeletionRequest::with('requester');

        if (!$user->isSuperAdmin()) {
            $query->where('requested_by', $user->id);
        }

        if ($status = $request->input('status')) {
            if (in_array($status, ['pending', 'approved', 'rejected'])) {
                $query->where('status', $status);
            }
        }

        $requests = $query->latest()->paginate(20)->withQueryString();
        $pendingCount = DeletionRequest::pending()->count();

        return view('admin.deletion-requests.index', compact('requests', 'pendingCount'));
    }

    /**
     * Staff submits a deletion request for a booking.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'      => ['required', 'in:booking'],
            'target_id' => ['required', 'integer'],
            'reason'    => ['required', 'string', 'max:500'],
        ]);

        $user = auth('admin')->user();

        // Prevent super_admin from making requests (they can delete directly)
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Super admins can delete directly.');
        }

        // Check target exists
        if ($validated['type'] === 'booking') {
            $booking = Booking::find($validated['target_id']);
            if (!$booking) {
                return back()->with('error', 'Booking not found.');
            }
            $targetLabel = $booking->booking_number;
        }

        // Prevent duplicate pending requests
        $exists = DeletionRequest::where('type', $validated['type'])
            ->where('target_id', $validated['target_id'])
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return back()->with('warning', 'A deletion request for this item is already pending.');
        }

        DeletionRequest::create([
            'requested_by' => $user->id,
            'type'         => $validated['type'],
            'target_id'    => $validated['target_id'],
            'target_label' => $targetLabel,
            'reason'       => strip_tags($validated['reason']),
        ]);

        return back()->with('success', 'Deletion request submitted. An administrator will review it.');
    }

    /**
     * Super admin approves a deletion request → actually deletes the target.
     */
    public function approve(Request $request, DeletionRequest $deletionRequest)
    {
        $user = auth('admin')->user();
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        if ($deletionRequest->status !== 'pending') {
            return back()->with('warning', 'This request has already been reviewed.');
        }

        $reviewNote = strip_tags($request->input('review_note', ''));

        // Execute the deletion
        if ($deletionRequest->type === 'booking') {
            $booking = Booking::find($deletionRequest->target_id);
            if ($booking) {
                // Decrement booked seats
                if ($booking->schedule_id && $booking->status === 'confirmed') {
                    $schedule = TourSchedule::find($booking->schedule_id);
                    if ($schedule) {
                        $schedule->decrement('booked_seats', $booking->total_guests);
                        if ($schedule->status === 'sold_out' && $schedule->booked_seats < $schedule->available_seats) {
                            $schedule->update(['status' => 'active']);
                        }
                    }
                }
                $booking->delete();
            }
        }

        $deletionRequest->update([
            'status'      => 'approved',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_note' => $reviewNote,
        ]);

        return back()->with('success', "Deletion request approved — {$deletionRequest->target_label} has been deleted.");
    }

    /**
     * Super admin rejects a deletion request.
     */
    public function reject(Request $request, DeletionRequest $deletionRequest)
    {
        $user = auth('admin')->user();
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        if ($deletionRequest->status !== 'pending') {
            return back()->with('warning', 'This request has already been reviewed.');
        }

        $validated = $request->validate([
            'review_note' => ['required', 'string', 'max:500'],
        ]);

        $deletionRequest->update([
            'status'      => 'rejected',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_note' => strip_tags($validated['review_note']),
        ]);

        return back()->with('success', "Deletion request for {$deletionRequest->target_label} has been rejected.");
    }
}
