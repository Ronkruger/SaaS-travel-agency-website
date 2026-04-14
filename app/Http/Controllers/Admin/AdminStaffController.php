<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminStaffController extends Controller
{
    /** GET /admin/staff */
    public function index()
    {
        $staff = AdminUser::orderByRaw("FIELD(role,'super_admin','staff')")
                          ->orderBy('name')
                          ->get();

        return view('admin.staff.index', [
            'staff'       => $staff,
            'permissions' => AdminUser::PERMISSIONS,
        ]);
    }

    /** GET /admin/staff/{adminUser}/edit */
    public function edit(AdminUser $adminUser)
    {
        return view('admin.staff.edit', [
            'member'      => $adminUser,
            'permissions' => AdminUser::PERMISSIONS,
            'defaults'    => AdminUser::DEFAULT_STAFF_PERMISSIONS,
        ]);
    }

    /** PUT /admin/staff/{adminUser} */
    public function update(Request $request, AdminUser $adminUser)
    {
        // Only super_admins can change roles / permissions
        abort_unless(auth('admin')->user()->isSuperAdmin(), 403);

        $validated = $request->validate([
            'role'        => ['required', Rule::in(['super_admin', 'staff'])],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::in(array_keys(AdminUser::PERMISSIONS))],
        ]);

        // Prevent de-promoting yourself
        if ($adminUser->is($this->auth()) && $validated['role'] !== 'super_admin') {
            return back()->with('error', 'You cannot remove your own super admin role.');
        }

        $adminUser->update([
            'role'        => $validated['role'],
            'permissions' => $validated['role'] === 'super_admin' ? null : ($validated['permissions'] ?? []),
        ]);

        return redirect()->route('admin.staff.index')
                         ->with('success', "{$adminUser->name}'s permissions have been updated.");
    }

    private function auth(): AdminUser
    {
        return auth('admin')->user();
    }
}
