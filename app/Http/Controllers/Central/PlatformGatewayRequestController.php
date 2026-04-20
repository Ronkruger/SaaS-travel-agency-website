<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\GatewayRequest;
use Illuminate\Http\Request;

class PlatformGatewayRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = GatewayRequest::with('tenant')->latest();

        if ($request->filled('status')) {
            $query->byStatus($request->input('status'));
        }

        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where(function ($sub) use ($q) {
                $sub->where('gateway_name', 'like', "%{$q}%")
                    ->orWhereHas('tenant', function ($t) use ($q) {
                        $t->where('name', 'like', "%{$q}%")
                          ->orWhere('company_name', 'like', "%{$q}%");
                    });
            });
        }

        $requests = $query->paginate(20)->withQueryString();

        $stats = [
            'total'       => GatewayRequest::count(),
            'pending'     => GatewayRequest::where('status', 'pending')->count(),
            'in_progress' => GatewayRequest::where('status', 'in_progress')->count(),
            'approved'    => GatewayRequest::where('status', 'approved')->count(),
            'rejected'    => GatewayRequest::where('status', 'rejected')->count(),
        ];

        return view('central.platform.gateway-requests.index', compact('requests', 'stats'));
    }

    public function update(Request $request, GatewayRequest $gatewayRequest)
    {
        $request->validate([
            'status'      => ['required', 'string', 'in:pending,in_progress,approved,rejected'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $gatewayRequest->update([
            'status'      => $request->input('status'),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        return back()->with('success', 'Gateway request updated successfully.');
    }
}
