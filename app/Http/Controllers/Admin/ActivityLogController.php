<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    public function index(Request $request)
    {
        $query = AdminActivityLog::with('adminUser')->latest();

        if ($action = $request->input('action')) {
            $query->where('action', 'like', "%{$action}%");
        }
        if ($subject = $request->input('subject')) {
            $query->where('subject_type', $subject);
        }
        if ($adminId = $request->input('admin_id')) {
            $query->where('admin_user_id', (int) $adminId);
        }
        if ($date = $request->input('date')) {
            $query->whereDate('created_at', $date);
        }

        $logs = $query->paginate(50)->withQueryString();

        $admins  = \App\Models\AdminUser::orderBy('name')->get(['id', 'name']);
        $actions = AdminActivityLog::selectRaw('SUBSTRING_INDEX(action, ".", 1) as module')
            ->distinct()
            ->pluck('module');

        return view('admin.activity-log.index', compact('logs', 'admins', 'actions'));
    }
}
