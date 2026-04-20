<?php

if (!function_exists('xendit_secret_key')) {
    /**
     * Get Xendit secret key: DB setting first, then env fallback.
     */
    function xendit_secret_key(): ?string
    {
        $dbKey = \App\Models\Setting::get('xendit_secret_key');
        return !empty($dbKey) ? $dbKey : config('xendit.secret_key');
    }
}

if (!function_exists('xendit_webhook_token')) {
    /**
     * Get Xendit webhook token: DB setting first, then env fallback.
     */
    function xendit_webhook_token(): ?string
    {
        $dbToken = \App\Models\Setting::get('xendit_webhook_token');
        return !empty($dbToken) ? $dbToken : config('xendit.webhook_token');
    }
}

if (!function_exists('cdn_url')) {
    /**
     * Resolve a stored media path to a publicly accessible URL.
     *
     * Handles both Cloudinary URLs (stored as full https:// URLs) and
     * legacy local files stored under public/storage (stored as relative paths).
     *
     * @param  string|null  $path      The stored path or URL.
     * @param  string       $fallback  URL to return when $path is empty.
     * @return string
     */
    function cdn_url(?string $path, string $fallback = ''): string
    {
        if (!$path) {
            return $fallback;
        }

        // Full URL (Cloudinary or any CDN) — return as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Legacy local file symlinked under public/storage
        return asset('storage/' . ltrim($path, '/'));
    }
}

if (!function_exists('parse_setting_array')) {
    /**
     * Parse a Setting value that may be a JSON array or a legacy single string.
     * Returns a flat, filtered array of non-empty strings.
     */
    function parse_setting_array(?string $value): array
    {
        if (empty($value)) return [];
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map('trim', $decoded)));
        }
        return [trim($value)];
    }
}

if (!function_exists('youtube_embed_url')) {
    /**
     * Convert any YouTube URL format to an embed URL.
     *
     * Accepts:
     *   https://www.youtube.com/watch?v=VIDEO_ID
     *   https://www.youtube.com/shorts/VIDEO_ID
     *   https://youtu.be/VIDEO_ID
     *   https://www.youtube.com/embed/VIDEO_ID  (already correct, returned as-is)
     *
     * @param  string  $url
     * @return string
     */
    function youtube_embed_url(string $url): string
    {
        if (empty($url)) {
            return '';
        }

        // Already an embed URL
        if (str_contains($url, 'youtube.com/embed/')) {
            return $url;
        }

        // Shorts: youtube.com/shorts/VIDEO_ID
        if (preg_match('#youtube\.com/shorts/([a-zA-Z0-9_-]+)#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        // Watch: youtube.com/watch?v=VIDEO_ID
        if (preg_match('#[?&]v=([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        // Short link: youtu.be/VIDEO_ID
        if (preg_match('#youtu\.be/([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        return $url;
    }
}

if (!function_exists('video_embed_url')) {
    /**
     * Convert a YouTube, YouTube Shorts, youtu.be, Google Drive, or Vimeo
     * share/watch URL into an embeddable iframe src.
     * Returns empty string if the URL is not recognised as a supported video.
     */
    function video_embed_url(string $url): string
    {
        if (empty($url)) return '';

        // Already an embed URL
        if (str_contains($url, 'youtube.com/embed/') || str_contains($url, 'drive.google.com/file/d/') && str_contains($url, '/preview')) {
            return $url;
        }

        // YouTube Shorts
        if (preg_match('#youtube\.com/shorts/([a-zA-Z0-9_-]+)#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        // YouTube watch
        if (preg_match('#[?&]v=([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        // youtu.be
        if (preg_match('#youtu\.be/([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        // Google Drive share link  → /preview embed
        if (preg_match('#drive\.google\.com/file/d/([a-zA-Z0-9_-]+)#', $url, $m)) {
            return 'https://drive.google.com/file/d/' . $m[1] . '/preview';
        }
        // Vimeo
        if (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }

        return '';
    }
}

if (!function_exists('facebook_embed_url')) {
    /**
     * Convert a Facebook post/video/reel URL into a Facebook embed URL.
     * Returns empty string if not recognised.
     */
    function facebook_embed_url(string $url): string
    {
        if (empty($url)) return '';
        // Already a plugin/post or plugin/video embed URL
        if (str_contains($url, 'facebook.com/plugins/')) {
            return $url;
        }
        // Any facebook.com URL → use oEmbed-style iframe plugin
        if (preg_match('#facebook\.com/#', $url)) {
            return 'https://www.facebook.com/plugins/post.php?href=' . urlencode($url) . '&show_text=true&width=500&appId=';
        }
        return '';
    }
}
