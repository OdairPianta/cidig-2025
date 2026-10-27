<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Single place that knows where admin uploads live: the `public` disk locally and a
 * DigitalOcean Spaces disk in production (App Platform's own disk is wiped on every deploy).
 */
class Uploads
{
    public static function disk(): string
    {
        return config('filesystems.uploads_disk', 'public');
    }

    public static function storage(): Filesystem
    {
        return Storage::disk(self::disk());
    }

    /**
     * Public URL of an uploaded (or legacy, committed under public/) file.
     */
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        // Legacy images shipped with the code live under public/ (no remote call needed).
        if (file_exists(public_path(ltrim($path, '/')))) {
            return url($path);
        }

        return self::storage()->url($path);
    }

    /** Parses PHP ini sizes such as "2M" or "50M" into bytes. */
    public static function iniBytes(string $value): int
    {
        $value = trim($value);
        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }

    /** Largest single upload PHP will accept on this server (the smaller of the two ini limits). */
    public static function serverLimitBytes(): int
    {
        $limits = array_filter([
            self::iniBytes((string) ini_get('upload_max_filesize')),
            self::iniBytes((string) ini_get('post_max_size')),
        ]);

        return $limits ? min($limits) : PHP_INT_MAX;
    }

    public static function humanBytes(int $bytes): string
    {
        return $bytes >= 1024 ** 2 ? round($bytes / 1024 ** 2, 1) . ' MB' : round($bytes / 1024) . ' KB';
    }
}
