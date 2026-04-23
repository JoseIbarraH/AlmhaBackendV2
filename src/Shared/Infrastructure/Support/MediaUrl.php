<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Resolves stored media paths to publicly-accessible URLs.
 *
 * Stored values in the DB may be either:
 *   - A relative path on the configured disk ("designs/abc.jpg")
 *   - A full absolute URL ("https://cdn.example.com/abc.jpg")
 *
 * This helper normalizes both cases so presenters can blindly pass what's
 * in the DB and get back something a browser can load.
 */
final class MediaUrl
{
    public static function resolve(?string $path): string
    {
        if ($path === null || $path === '') {
            return '';
        }

        // Already an absolute URL (or protocol-relative) — trust it.
        if (preg_match('/^(https?:)?\/\//i', $path)) {
            return $path;
        }

        // Resolve via the default filesystem disk (typically s3 in prod).
        return Storage::disk(config('filesystems.default'))->url(ltrim($path, '/'));
    }

    /**
     * Reverse of resolve(): normalizes any stored value (full URL or relative
     * path) back to the relative path that the storage disk understands.
     * Use this before calling Storage::delete()/exists() etc.
     */
    public static function toRelativePath(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Plain relative path — return as-is (trim leading slash).
        if (!preg_match('/^(https?:)?\/\//i', $value)) {
            return ltrim($value, '/');
        }

        // Strip the configured disk's base URL.
        $baseUrl = rtrim((string) config('filesystems.disks.s3.url'), '/') . '/';
        if (str_starts_with($value, $baseUrl)) {
            return substr($value, strlen($baseUrl));
        }

        // Fallback: use everything after the domain.
        $parsed = parse_url($value);
        return ltrim($parsed['path'] ?? '', '/');
    }
}
