<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Caching layer for /api/client/* endpoints.
 *
 * Works with any cache driver (database/file/redis) — does NOT require tags.
 * Tracks the keys belonging to each "group" in a registry so we can flush
 * a whole group at once when underlying data changes.
 *
 * Groups:
 *   - maintenance    flushed when settings change
 *   - navbar         flushed when settings/design/procedure/team change
 *   - home           flushed when design changes
 *   - contact_data   flushed when settings/procedure change
 *   - blog           flushed when blog/blog category changes
 *   - procedure      flushed when procedure or its nested data changes
 *   - member         flushed when team data changes
 */
final class ClientCache
{
    public const TTL_SHORT  = 60;    // 1 min  — fast-changing, e.g. maintenance flag
    public const TTL_MEDIUM = 300;   // 5 min  — list endpoints with filters
    public const TTL_LONG   = 600;   // 10 min — stable read-heavy endpoints

    private const PREFIX        = 'almha_client:';
    private const REGISTRY_TTL  = 86400; // 24h

    public static function remember(string $group, string $key, int $ttl, Closure $callback): mixed
    {
        $cacheKey = self::PREFIX . $key;
        self::trackKey($group, $cacheKey);

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    public static function forgetKey(string $key): void
    {
        Cache::forget(self::PREFIX . $key);
    }

    public static function flushGroups(string ...$groups): void
    {
        foreach ($groups as $group) {
            $registryKey = self::registryKey($group);
            $keys = Cache::get($registryKey, []);

            if (!is_array($keys)) {
                Cache::forget($registryKey);
                continue;
            }

            foreach ($keys as $key) {
                Cache::forget($key);
            }

            Cache::forget($registryKey);
        }
    }

    public static function flushAll(): void
    {
        self::flushGroups('maintenance', 'navbar', 'home', 'contact_data', 'blog', 'procedure', 'member');
    }

    private static function trackKey(string $group, string $cacheKey): void
    {
        $registryKey = self::registryKey($group);
        $keys = Cache::get($registryKey, []);

        if (!is_array($keys)) {
            $keys = [];
        }

        if (!in_array($cacheKey, $keys, true)) {
            $keys[] = $cacheKey;
            Cache::put($registryKey, $keys, self::REGISTRY_TTL);
        }
    }

    private static function registryKey(string $group): string
    {
        return self::PREFIX . '_keys:' . $group;
    }
}
