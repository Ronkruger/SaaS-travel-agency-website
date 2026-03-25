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
