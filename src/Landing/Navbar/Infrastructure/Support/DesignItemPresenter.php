<?php

declare(strict_types=1);

namespace Src\Landing\Navbar\Infrastructure\Support;

use Src\Admin\Design\Infrastructure\Models\EloquentDesignModel;
use Src\Shared\Infrastructure\Support\MediaUrl;

final class DesignItemPresenter
{
    /**
     * Fetch items for a given design key, shaped as { type, image, title, subtitle }.
     */
    public static function itemsFor(string $key, string $lang): array
    {
        $design = EloquentDesignModel::where('key', $key)
            ->where('status', 'active')
            ->with(['items' => fn ($q) => $q->where('status', 'active')->orderBy('order')])
            ->first();

        if (!$design) {
            return [];
        }

        return $design->items->map(function ($item) use ($lang) {
            $item->load(['translations' => fn ($q) => $q->where('lang', $lang)]);
            $t = $item->translations->firstWhere('lang', $lang)
                ?? $item->translations->first();

            return [
                'type'     => $item->media_type ?? 'image',
                'image'    => MediaUrl::resolve($item->media_path),
                'title'    => $t?->title ?? '',
                'subtitle' => $t?->subtitle ?? '',
            ];
        })->values()->toArray();
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
}
