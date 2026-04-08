<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Raise input variable limit for large admin forms (tour stops, itinerary, etc.)
        @ini_set('max_input_vars', 5000);

        // Share branding globals with every view (guard against table not yet migrated)
        try {
            if (Schema::hasTable('settings')) {
                View::share('brandLogoUrl',     Setting::logoUrl('logo_path'));
                View::share('brandLogoDarkUrl', Setting::logoUrl('logo_dark_path'));
                View::share('brandFaviconUrl',  Setting::logoUrl('favicon_path'));
                View::share('brandName',        Setting::get('company_name', 'Discover Group'));
                View::share('brandTagline',     Setting::get('company_tagline', ''));
                // Homepage customisation
                View::share('promoBannerUrl',  Setting::logoUrl('promo_banner_path'));
                View::share('promoBannerLink', Setting::get('promo_banner_link', ''));
                $fbItems = parse_setting_array(Setting::get('fb_embed_code', ''));
                $ytRaw   = parse_setting_array(Setting::get('yt_embed_url', ''));
                $ytItems = array_map('youtube_embed_url', $ytRaw);
                View::share('fbEmbeds',   $fbItems);
                View::share('ytEmbeds',   $ytItems);
                View::share('fbEmbedUrl', $fbItems[0] ?? '');
                View::share('ytEmbedUrl', $ytItems[0] ?? '');
            } else {
                View::share('brandLogoUrl',     null);
                View::share('brandLogoDarkUrl', null);
                View::share('brandFaviconUrl',  null);
                View::share('brandName',        'Discover Group');
                View::share('brandTagline',     '');
                View::share('promoBannerUrl',   null);
                View::share('promoBannerLink',  '');
                View::share('fbEmbeds',         []);
                View::share('ytEmbeds',         []);
                View::share('fbEmbedUrl',       '');
                View::share('ytEmbedUrl',       '');
        } catch (\Throwable) {
            View::share('brandLogoUrl',     null);
            View::share('brandLogoDarkUrl', null);
            View::share('brandFaviconUrl',  null);
            View::share('brandName',        'Discover Group');
            View::share('brandTagline',     '');
            View::share('promoBannerUrl',   null);
            View::share('promoBannerLink',  '');
            View::share('fbEmbeds',         []);
            View::share('ytEmbeds',         []);
            View::share('fbEmbedUrl',       '');
            View::share('ytEmbedUrl',       '');
        }
    }
}
