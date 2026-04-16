<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    /**
     * Preview receipt PDF in-browser.
     */
    public function preview(Payment $payment)
    {
        $data = $this->buildData($payment);

        $pdf = Pdf::loadView('admin.payments.receipt-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 150,
            ]);

        return $pdf->stream("receipt-{$payment->transaction_id}.pdf");
    }

    /**
     * Force-download receipt PDF.
     */
    public function download(Payment $payment)
    {
        $data = $this->buildData($payment);

        $pdf = Pdf::loadView('admin.payments.receipt-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 150,
            ]);

        return $pdf->download("receipt-{$payment->transaction_id}.pdf");
    }

    private function buildData(Payment $payment): array
    {
        $payment->load(['booking.tour']);
        $booking = $payment->booking;
        $channel = strtoupper(
            $payment->gateway_response['payment_channel']
            ?? $payment->gateway_response['payment_method']
            ?? $payment->method
        );

        return [
            'payment'  => $payment,
            'booking'  => $booking,
            'channel'  => $channel,
            'settings' => [
                'company_name'  => Setting::get('company_name', 'Discover Group'),
                'tagline'       => Setting::get('company_tagline', 'Travel & Tours'),
                'logo_url'      => Setting::get('pdf_logo_url') ?? Setting::get('logo_path'),
                'accent_color'  => Setting::get('pdf_accent_color', '#1e3a8a'),
                'contact_email' => Setting::get('pdf_contact_email', ''),
                'contact_phone' => Setting::get('pdf_contact_phone', ''),
            ],
        ];
    }
}
