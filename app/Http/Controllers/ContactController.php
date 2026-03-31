<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email', 'max:150'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        try {
            Mail::raw(
                implode("\n", [
                    "Name:    {$validated['name']}",
                    "Email:   {$validated['email']}",
                    "Phone:   " . ($validated['phone'] ?? '—'),
                    "Subject: {$validated['subject']}",
                    "",
                    $validated['message'],
                ]),
                function ($msg) use ($validated) {
                    $msg->to('inquiry@discovergrp.com')
                        ->replyTo($validated['email'], $validated['name'])
                        ->subject('[Contact Form] ' . $validated['subject'] . ' — ' . $validated['name']);
                }
            );
        } catch (\Throwable $e) {
            Log::error('Contact form mail failed', ['error' => $e->getMessage()]);
        }

        return back()->with('contact_success', 'Thank you! Your message has been sent. We\'ll get back to you within 24 hours.');
    }
}
