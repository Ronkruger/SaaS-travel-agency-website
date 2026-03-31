<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DIYTourQuoteMail;
use App\Models\DIYTourSession;
use App\Models\DIYTourItinerary;
use App\Models\DIYTourQuote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DIYTourController extends Controller
{
    // -------------------------------------------------------------------------
    // List all DIY sessions (admin dashboard)
    // -------------------------------------------------------------------------

    public function index(Request $request)
    {
        $sessions = DIYTourSession::with(['user', 'latestItinerary'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%'));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.diy.index', compact('sessions'));
    }

    // -------------------------------------------------------------------------
    // Show a single session with full itinerary
    // -------------------------------------------------------------------------

    public function show(DIYTourSession $diySession)
    {
        $diySession->load(['user', 'itineraries.quotes', 'collaborators.user']);
        $itinerary = $diySession->latestItinerary;

        return view('admin.diy.show', compact('diySession', 'itinerary'));
    }

    // -------------------------------------------------------------------------
    // Generate a formal quote for a session
    // -------------------------------------------------------------------------

    public function generateQuote(Request $request, DIYTourSession $diySession)
    {
        $validated = $request->validate([
            'price_override' => 'nullable|numeric|min:1000',
            'valid_days'     => 'nullable|integer|min:1|max:90',
            'terms'          => 'nullable|string|max:2000',
        ]);

        $itinerary = $diySession->latestItinerary;
        if (!$itinerary) {
            return back()->with('error', 'No itinerary found for this session.');
        }

        // Use admin override price or auto-calculated pricing
        $pricing    = $itinerary->pricing_data ?? [];
        $quotePrice = isset($validated['price_override'])
            ? (float) $validated['price_override']
            : (float) ($pricing['total_per_person'] ?? 0);

        $quote = DIYTourQuote::create([
            'itinerary_id'     => $itinerary->id,
            'quoted_price_php' => $quotePrice,
            'valid_until'      => now()->addDays((int) ($validated['valid_days'] ?? 7)),
            'terms_conditions' => $validated['terms'] ?? null,
            'generated_by'     => (string) Auth::id(),
            'status'           => 'pending',
        ]);

        $diySession->update(['status' => 'quoted']);

        // Send quote email to the client if they have an account
        $client = $diySession->user;
        if ($client && $client->email) {
            try {
                // Eager-load latestItinerary so the Mailable doesn't run extra queries
                $diySession->loadMissing('latestItinerary');
                Mail::to($client->email)->send(new DIYTourQuoteMail($quote, $diySession));
            } catch (\Throwable $e) {
                Log::error('DIY quote email failed', [
                    'session_id' => $diySession->id,
                    'quote_id'   => $quote->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', 'Quote generated successfully. The client has been notified by email.');
    }

    // -------------------------------------------------------------------------
    // Update session status
    // -------------------------------------------------------------------------

    public function updateStatus(Request $request, DIYTourSession $diySession)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,pending_review,quoted,booked',
        ]);

        $diySession->update(['status' => $validated['status']]);

        return back()->with('success', 'Status updated.');
    }

    // -------------------------------------------------------------------------
    // Approve / Reject
    // -------------------------------------------------------------------------

    public function approve(DIYTourSession $diySession)
    {
        $diySession->update(['admin_status' => 'approved']);
        return redirect()->route('admin.diy.show', $diySession)
            ->with('success', 'DIY tour request approved. The client will see the updated status immediately.');
    }

    public function reject(DIYTourSession $diySession)
    {
        $diySession->update(['admin_status' => 'rejected']);
        return redirect()->route('admin.diy.show', $diySession)
            ->with('success', 'DIY tour request rejected.');
    }

    public function destroy(DIYTourSession $diySession)
    {
        $diySession->delete();

        return redirect()->route('admin.diy.index')
            ->with('success', 'DIY tour session deleted.');
    }
}
