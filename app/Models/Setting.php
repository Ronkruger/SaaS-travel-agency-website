<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public    $incrementing = false;
    protected $keyType     = 'string';

    protected $fillable = ['key', 'value'];

    // ── static helpers ───────────────────────────────────────

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $row = static::find($key);
            return $row ? $row->value : $default;
        });
    }

    /**
     * Set (upsert) a setting and bust the cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting.{$key}");
    }

    /**
     * Return the public URL for a stored image setting.
     * Handles both full Cloudinary URLs (https://...) and legacy local storage paths.
     */
    public static function logoUrl(string $key = 'logo_path'): ?string
    {
        $path = static::get($key);
        if (!$path) return null;
        // Already a full URL (Cloudinary or any CDN)
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        // Legacy local storage path
        return asset('storage/' . $path);
    }
}
