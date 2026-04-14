<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::with('createdBy')->latest();

        if ($search = $request->input('search')) {
            $query->where('code', 'like', '%' . strip_tags($search) . '%');
        }
        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true)
                    ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', today()));
            } elseif ($request->input('status') === 'inactive') {
                $query->where(fn($q) =>
                    $q->where('is_active', false)
                      ->orWhere('expires_at', '<', today())
                );
            }
        }

        $coupons = $query->paginate(20)->withQueryString();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'        => ['required', 'string', 'max:50', 'alpha_dash', 'unique:coupons,code'],
            'type'        => ['required', 'in:percent,fixed'],
            'value'       => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'min_spend'   => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at'  => ['nullable', 'date', 'after:today'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['boolean'],
        ]);

        // Extra: percent type can't exceed 100
        if ($validated['type'] === 'percent' && $validated['value'] > 100) {
            return back()->withErrors(['value' => 'Percent discount cannot exceed 100%.]'])->withInput();
        }

        $coupon = Coupon::create([
            'code'        => strtoupper($validated['code']),
            'type'        => $validated['type'],
            'value'       => $validated['value'],
            'min_spend'   => $validated['min_spend'] ?? 0,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'expires_at'  => $validated['expires_at'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
            'created_by'  => auth('admin')->id(),
        ]);

        AdminActivityLog::record('coupon.created', $coupon, "Coupon {$coupon->code} created.");

        return back()->with('success', "Coupon {$coupon->code} created.");
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'type'        => ['required', 'in:percent,fixed'],
            'value'       => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'min_spend'   => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at'  => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['boolean'],
        ]);

        if ($validated['type'] === 'percent' && $validated['value'] > 100) {
            return back()->withErrors(['value' => 'Percent discount cannot exceed 100%.'])->withInput();
        }

        $coupon->update([
            'type'        => $validated['type'],
            'value'       => $validated['value'],
            'min_spend'   => $validated['min_spend'] ?? 0,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'expires_at'  => $validated['expires_at'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
        ]);

        AdminActivityLog::record('coupon.updated', $coupon, "Coupon {$coupon->code} updated.");

        return back()->with('success', "Coupon {$coupon->code} updated.");
    }

    public function destroy(Coupon $coupon)
    {
        $code = $coupon->code;
        $coupon->delete();
        AdminActivityLog::record('coupon.deleted', null, "Coupon {$code} deleted.");
        return back()->with('success', "Coupon {$code} deleted.");
    }

    /**
     * AJAX: validate a coupon code against a subtotal.
     * POST /admin/coupons/check
     */
    public function check(Request $request)
    {
        $validated = $request->validate([
            'code'     => ['required', 'string', 'max:50'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $coupon = Coupon::where('code', strtoupper($validated['code']))->first();

        if (!$coupon || !$coupon->isValid((float) $validated['subtotal'])) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired coupon code.']);
        }

        $discount = $coupon->discountFor((float) $validated['subtotal']);

        return response()->json([
            'valid'    => true,
            'code'     => $coupon->code,
            'type'     => $coupon->type,
            'value'    => $coupon->value,
            'discount' => $discount,
            'message'  => ($coupon->type === 'percent' ? $coupon->value . '% off' : '₱' . number_format($coupon->value, 2) . ' off'),
        ]);
    }
}
