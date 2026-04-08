<?php

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
