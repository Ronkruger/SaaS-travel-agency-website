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
            'fbEmbedCode'     => Setting::get('fb_embed_code', ''),
            'ytEmbedUrl'      => Setting::get('yt_embed_url', ''),
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
            'promo_banner_link' => ['nullable', 'string', 'max:500'],
            'fb_embed_code'     => ['nullable', 'string', 'max:2000'],
            'yt_embed_url'      => ['nullable', 'url', 'max:500'],
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
            Setting::set('fb_embed_code', $request->fb_embed_code);
        }
        if ($request->has('yt_embed_url')) {
            Setting::set('yt_embed_url', $request->yt_embed_url);
        }

        $this->handleUpload($request, 'logo',         'logo_path',         'logos');
        $this->handleUpload($request, 'logo_dark',    'logo_dark_path',    'logos');
        $this->handleUpload($request, 'favicon',      'favicon_path',      'logos');
        $this->handleUpload($request, 'promo_banner', 'promo_banner_path', 'banners');

        return back()->with('success', 'Settings saved successfully.');
    }

    public function deleteLogo(Request $request)
    {
        $key = $request->validate(['key' => 'required|in:logo_path,logo_dark_path,favicon_path,promo_banner_path'])['key'];

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
     * Deletes the previous stored value (Cloudinary URL or legacy local path).
     */
    private function handleUpload(Request $request, string $field, string $settingKey, string $dir): void
    {
        if (!$request->hasFile($field) || !$request->file($field)->isValid()) {
            return;
        }

        $file = $request->file($field);

        // Defence-in-depth MIME check
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME, true)
            && $file->getMimeType() !== 'image/x-icon') {
            return;
        }

        // Delete previous value (Cloudinary URL or legacy local path)
        $old = Setting::get($settingKey);
        if ($old) {
            $this->cloudinaryDelete($old);
        }

        // Upload to Cloudinary and store the returned secure URL
        $secureUrl = $file->storeOnCloudinary($dir)->getSecurePath();
        Setting::set($settingKey, $secureUrl);
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
