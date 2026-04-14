<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientEmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ClientEmailLog::latest();

        if ($email = $request->input('email')) {
            $query->where('to_email', 'like', '%' . strip_tags($email) . '%');
        }
        if ($type = $request->input('type')) {
            $allowedTypes = [
                'BookingConfirmationMail',
                'BookingReservationMail',
                'BookingRejectionMail',
                'PaymentFollowupMail',
                'DIYTourQuoteMail',
                'OtpMail',
            ];
            if (in_array($type, $allowedTypes)) {
                $query->where('mail_class', $type);
            }
        }
        if ($booking = $request->input('booking')) {
            $query->where('booking_number', 'like', '%' . strip_tags($booking) . '%');
        }
        if ($date = $request->input('date')) {
            $query->whereDate('created_at', $date);
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('admin.email-log.index', compact('logs'));
    }
}
