<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    private const ALLOWED_MIME = ['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'];
    private const MAX_KB       = 2048; // 2 MB

    public function index()
    {
        return view('admin.settings.index', [
            'logoUrl'         => Setting::logoUrl('logo_path'),
            'logoDarkUrl'     => Setting::logoUrl('logo_dark_path'),
            'faviconUrl'      => Setting::logoUrl('favicon_path'),
            'companyName'     => Setting::get('company_name', 'Discover Group'),
            'tagline'         => Setting::get('company_tagline', ''),
            'promoBannerUrl'  => Setting::logoUrl('promo_banner_path'),
            'promoBannerLink' => Setting::get('promo_banner_link', ''),
            'fbEmbedItems'    => parse_setting_array(Setting::get('fb_embed_code', '')),
            'ytEmbedItems'    => parse_setting_array(Setting::get('yt_embed_url', '')),
            // PDF Settings
            'pdfLogoUrl'      => Setting::get('pdf_logo_url'),
            'pdfAccentColor'  => Setting::get('pdf_accent_color', '#1e3a8a'),
            'pdfHeaderText'   => Setting::get('pdf_header_text', 'OFFICIAL BOOKING CONFIRMATION'),
            'pdfFooterText'   => Setting::get('pdf_footer_text', 'Thank you for choosing us. This document serves as your official booking confirmation.'),
            'pdfShowPayments' => Setting::get('pdf_show_payments', '1'),
            'pdfContactEmail' => Setting::get('pdf_contact_email', ''),
            'pdfContactPhone' => Setting::get('pdf_contact_phone', ''),
            'pdfContactAddr'  => Setting::get('pdf_contact_address', ''),
            'pdfFacebookUrl'  => Setting::get('pdf_facebook_url', ''),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name'      => ['nullable', 'string', 'max:100'],
            'company_tagline'   => ['nullable', 'string', 'max:200'],
            'logo'              => ['nullable', 'file', 'max:' . self::MAX_KB, 'mimes:png,jpg,jpeg,svg,webp'],
            'logo_dark'         => ['nullable', 'file', 'max:' . self::MAX_KB, 'mimes:png,jpg,jpeg,svg,webp'],
            'favicon'           => ['nullable', 'file', 'max:512', 'mimes:png,jpg,jpeg,svg,webp,ico'],
            'promo_banner'      => ['nullable', 'file', 'max:4096', 'mimes:png,jpg,jpeg,webp'],
            // SECURITY: validate as URL with http(s) scheme only to prevent open
            // redirects and javascript:/data: URI injection (CWE-601 / CWE-79).
            'promo_banner_link' => ['nullable', 'url', 'max:500', function ($attr, $val, $fail) {
                if ($val && !in_array(strtolower((string) parse_url($val, PHP_URL_SCHEME)), ['http', 'https'], true)) {
                    $fail('The promo banner link must be a valid http(s) URL.');
                }
            }],
            'fb_embed_code'     => ['nullable', 'array'],
            'fb_embed_code.*'   => ['nullable', 'string', 'max:5000', function ($attr, $val, $fail) {
                if ($val && !preg_match('#^\s*<iframe[^>]+src=["\']https://www\.facebook\.com/#i', $val)) {
                    $fail('Each Facebook embed must be a valid Facebook iframe embed code (src must point to www.facebook.com).');
                }
            }],
            'yt_embed_url'      => ['nullable', 'array'],
            'yt_embed_url.*'    => ['nullable', 'url', 'max:500'],
            // PDF settings
            'pdf_logo'             => ['nullable', 'file', 'max:' . self::MAX_KB, 'mimes:png,jpg,jpeg,svg,webp'],
            'pdf_accent_color'     => ['nullable', 'string', 'max:20'],
            'pdf_header_text'      => ['nullable', 'string', 'max:200'],
            'pdf_footer_text'      => ['nullable', 'string', 'max:500'],
            'pdf_show_payments'    => ['nullable', 'boolean'],
            'pdf_contact_email'    => ['nullable', 'email', 'max:200'],
            'pdf_contact_phone'    => ['nullable', 'string', 'max:50'],
            'pdf_contact_address'  => ['nullable', 'string', 'max:300'],
            'pdf_facebook_url'     => ['nullable', 'string', 'max:200'],
        ]);

        if ($request->filled('company_name')) {
            Setting::set('company_name', $request->company_name);
        }
        if ($request->has('company_tagline')) {
            Setting::set('company_tagline', $request->company_tagline);
        }
        if ($request->has('promo_banner_link')) {
            Setting::set('promo_banner_link', $request->promo_banner_link);
        }
        if ($request->has('fb_embed_code')) {
            $fbItems = array_values(array_filter(array_map('trim', (array) $request->fb_embed_code)));
            Setting::set('fb_embed_code', json_encode($fbItems));
        }
        if ($request->has('yt_embed_url')) {
            $ytItems = array_values(array_filter(array_map('trim', (array) $request->yt_embed_url)));
            Setting::set('yt_embed_url', json_encode($ytItems));
        }

        // PDF settings
        $pdfTextFields = [
            'pdf_accent_color', 'pdf_header_text', 'pdf_footer_text',
            'pdf_contact_email', 'pdf_contact_phone', 'pdf_contact_address', 'pdf_facebook_url',
        ];
        foreach ($pdfTextFields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->input($field));
            }
        }
        Setting::set('pdf_show_payments', $request->has('pdf_show_payments') ? '1' : '0');
        if ($request->hasFile('pdf_logo')) {
            $error = $this->handleUpload($request, 'pdf_logo', 'pdf_logo_url', 'logos');
            if ($error) {
                return back()->with('warning', 'PDF logo upload failed: ' . $error)->withInput();
            }
        }

        $uploadErrors = [];
        foreach ([
            'logo'         => ['logo_path',         'logos'],
            'logo_dark'    => ['logo_dark_path',    'logos'],
            'favicon'      => ['favicon_path',      'logos'],
            'promo_banner' => ['promo_banner_path', 'banners'],
        ] as $field => [$settingKey, $dir]) {
            $error = $this->handleUpload($request, $field, $settingKey, $dir);
            if ($error) {
                $uploadErrors[] = ucfirst(str_replace('_', ' ', $field)) . ': ' . $error;
            }
        }

        if (!empty($uploadErrors)) {
            return back()
                ->with('warning', 'Settings saved but image upload failed: ' . implode('; ', $uploadErrors))
                ->withInput();
        }

        return back()->with('success', 'Settings saved successfully.');
    }

    public function deleteLogo(Request $request)
    {
        $key = $request->validate(['key' => 'required|in:logo_path,logo_dark_path,favicon_path,promo_banner_path,pdf_logo_url'])['key'];

        $existing = Setting::get($key);
        if ($existing) {
            $this->cloudinaryDelete($existing);
        }
        Setting::set($key, null);

        return back()->with('success', 'Image removed.');
    }

    // ── private ───────────────────────────────────────────────

    /**
     * Upload a file to Cloudinary and store the secure URL in settings.
     * Returns null on success, or an error string on failure.
     * The old image is only deleted AFTER the new upload succeeds.
     */
    private function handleUpload(Request $request, string $field, string $settingKey, string $dir): ?string
    {
        if (!$request->hasFile($field)) {
            return null; // no file submitted — not an error
        }

        $file = $request->file($field);

        if (!$file->isValid()) {
            return 'Upload failed (PHP upload error ' . $file->getError() . ')';
        }

        // Defence-in-depth MIME check — also accept image/jpg used by some systems
        $mime = $file->getMimeType();
        $allowed = array_merge(self::ALLOWED_MIME, ['image/jpg', 'image/x-icon', 'image/vnd.microsoft.icon']);
        if (!in_array($mime, $allowed, true)) {
            return "Unsupported file type ({$mime}). Allowed: PNG, JPG, SVG, WebP.";
        }

        // Upload first — only delete old image if upload succeeds
        try {
            $secureUrl = $file->storeOnCloudinary($dir)->getSecurePath();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Cloudinary upload failed', [
                'field' => $field,
                'error' => $e->getMessage(),
            ]);
            return 'Cloudinary upload failed: ' . $e->getMessage();
        }

        // Delete the previous image only after successful upload
        $old = Setting::get($settingKey);
        if ($old) {
            try {
                $this->cloudinaryDelete($old);
            } catch (\Throwable) {
                // Non-fatal — old image cleanup failure should not block saving the new one
            }
        }

        Setting::set($settingKey, $secureUrl);
        return null;
    }

    /**
     * Delete an image stored either as a Cloudinary URL or a legacy local path.
     */
    private function cloudinaryDelete(?string $path): void
    {
        if (!$path) return;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $urlPath  = parse_url($path, PHP_URL_PATH);
            $publicId = preg_replace('#^.*/upload/(?:v\d+/)?#', '', $urlPath);
            $publicId = preg_replace('#\.[^.]+$#', '', $publicId);
            Cloudinary::destroy($publicId);
        } else {
            Storage::disk('public')->delete($path);
        }
    }
}
