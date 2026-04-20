<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingPdfController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    /**
     * Preview PDF in-browser (inline display).
     */
    public function preview(Booking $booking)
    {
        $booking->load(['tour', 'payment', 'payments', 'schedule', 'user']);
        $settings = $this->pdfSettings();

        $pdf = Pdf::loadView('admin.bookings.confirmation-pdf', compact('booking', 'settings'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 96,
            ]);

        return $pdf->stream("booking-{$booking->booking_number}.pdf");
    }

    /**
     * Force-download the PDF.
     */
    public function download(Booking $booking)
    {
        $booking->load(['tour', 'payment', 'payments', 'schedule', 'user']);
        $settings = $this->pdfSettings();

        $pdf = Pdf::loadView('admin.bookings.confirmation-pdf', compact('booking', 'settings'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 96,
            ]);

        return $pdf->download("booking-{$booking->booking_number}.pdf");
    }

    /**
     * Email the PDF as an attachment to the client.
     */
    public function email(Booking $booking)
    {
        $booking->load(['tour', 'payment', 'payments', 'schedule', 'user']);
        $settings = $this->pdfSettings();

        $pdf = Pdf::loadView('admin.bookings.confirmation-pdf', compact('booking', 'settings'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 96,
            ]);

        $pdfContent = $pdf->output();
        $filename   = "booking-{$booking->booking_number}.pdf";

        try {
            Mail::to($booking->contact_email)
                ->send(new BookingConfirmationMail($booking, null, false, null, $pdfContent, $filename));

            return back()->with('success', "Booking confirmation PDF sent to {$booking->contact_email}.");
        } catch (\Throwable $e) {
            Log::error('Failed to email booking PDF', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function pdfSettings(): array
    {
        return [
            'company_name'   => Setting::get('company_name', tenant()->company_name ?? tenant()->name ?? 'Your Agency'),
            'tagline'        => Setting::get('company_tagline', 'Travel & Tours'),
            'logo_url'       => Setting::get('pdf_logo_url') ?? Setting::get('logo_path'),
            'accent_color'   => Setting::get('pdf_accent_color', '#1e3a8a'),
            'header_text'    => Setting::get('pdf_header_text', 'OFFICIAL BOOKING CONFIRMATION'),
            'footer_text'    => Setting::get('pdf_footer_text', 'Thank you for choosing us. This document serves as your official booking confirmation.'),
            'show_payments'  => Setting::get('pdf_show_payments', '1') === '1',
            'contact_email'  => Setting::get('pdf_contact_email', ''),
            'contact_phone'  => Setting::get('pdf_contact_phone', ''),
            'contact_address'=> Setting::get('pdf_contact_address', ''),
            'facebook_url'   => Setting::get('pdf_facebook_url', ''),
        ];
    }
}
