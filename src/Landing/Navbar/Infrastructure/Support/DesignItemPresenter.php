<?php

declare(strict_types=1);

namespace Src\Landing\Navbar\Infrastructure\Support;

use Src\Admin\Design\Infrastructure\Models\EloquentDesignModel;
use Src\Shared\Infrastructure\Support\MediaUrl;

final class DesignItemPresenter
{
    /**
     * Fetch items for a given design key, shaped as { type, image, title, subtitle }.
     *
     * Optional $fallbackKey: if the primary design is inactive or has no items,
     * try this other key before giving up. Useful for `main_banner` →
     * `alternate_main_banner` so the admin can toggle between them by flipping
     * the active flag.
     */
    public static function itemsFor(string $key, string $lang, ?string $fallbackKey = null): array
    {
        $items = self::fetchActiveItems($key, $lang);

        // If primary is off or empty and we have a fallback, try it.
        if ($items === [] && $fallbackKey !== null) {
            $items = self::fetchActiveItems($fallbackKey, $lang);
        }

        return $items;
    }

    public static function settingsFor(string $key): array
    {
        $design = EloquentDesignModel::where('key', $key)->first();
        if (!$design) {
            return ['id' => 0, 'enabled' => false];
        }
        return [
            'id'      => (int) $design->id,
            'enabled' => $design->status === 'active',
        ];
    }

    /**
     * Returns the shaped items of an active design. Empty array if the design
     * is missing, inactive, or has no active media items.
     */
    private static function fetchActiveItems(string $key, string $lang): array
    {
        $design = EloquentDesignModel::where('key', $key)
            ->where('status', 'active')
            ->with(['items' => fn ($q) => $q->where('status', 'active')->orderBy('order')])
            ->first();

        if (!$design) {
            return [];
        }

        $items = $design->items
            ->map(function ($item) use ($lang) {
                $item->load(['translations' => fn ($q) => $q->where('lang', $lang)]);
                $t = $item->translations->firstWhere('lang', $lang)
                    ?? $item->translations->first();

                return [
                    'type'     => $item->media_type ?? 'image',
                    'image'    => MediaUrl::resolve($item->media_path),
                    'title'    => $t?->title ?? '',
                    'subtitle' => $t?->subtitle ?? '',
                ];
            })
            // Drop items without media — an "active" placeholder with empty
            // media_path should not be treated as renderable content.
            ->filter(fn ($item) => $item['image'] !== '')
            ->values()
            ->toArray();

        return $items;
    }
}
