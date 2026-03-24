<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'user');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->withCount('bookings')->latest()->paginate(15)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['bookings.tour', 'reviews.tour']);
        return view('admin.users.show', compact('user'));
    }
}
