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
}
