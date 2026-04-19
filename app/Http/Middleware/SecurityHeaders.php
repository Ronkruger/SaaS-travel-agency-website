<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Block MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Block this app from being iframe-embedded on other sites (clickjacking)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Limit referrer leakage
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // SECURITY: Force HTTPS for 1 year on this domain and all subdomains.
        // Mitigates SSL stripping attacks (CWE-319). Only emit on secure requests so
        // local HTTP development isn't broken.
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Disable browser features not used by this app.
        // webrtc=() prevents the WebRTC-based internal network scanning technique.
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=(), webrtc=()'
        );

        // Content Security Policy:
        // - unsafe-inline is required for inline <style> and <script> blocks used throughout Blade templates
        // - cdnjs.cloudflare.com covers Font Awesome, NProgress, GSAP
        // - fonts.googleapis.com / fonts.gstatic.com cover Google Fonts
        // - api.mapbox.com covers Mapbox GL JS, CSS, tiles, and geocoding
        // - frame-src allows Facebook and YouTube embeds used in the public site
        // - img-src allows Cloudinary (res.cloudinary.com), Facebook graph images, Mapbox tiles, and data URIs
        // - object-src 'none' blocks Flash/plugin-based attacks entirely
        // - base-uri 'self' prevents base tag hijacking
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' cdnjs.cloudflare.com api.mapbox.com",
            "style-src 'self' 'unsafe-inline' fonts.googleapis.com cdnjs.cloudflare.com api.mapbox.com",
            "font-src 'self' data: fonts.gstatic.com cdnjs.cloudflare.com",
            "img-src 'self' data: blob: https://res.cloudinary.com https://*.cloudinary.com https://graph.facebook.com https://*.fbcdn.net https://*.facebook.com https://*.mapbox.com",
            "frame-src 'self' www.facebook.com facebook.com www.youtube.com youtube.com www.youtube-nocookie.com",
            "connect-src 'self' https://*.mapbox.com https://api.mapbox.com",
            "worker-src 'self' blob:",
            "child-src 'self' blob:",
            "object-src 'none'",
            "base-uri 'self'",
        ]));

        return $response;
    }
}
