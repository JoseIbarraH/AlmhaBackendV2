<?php

declare(strict_types=1);

namespace Src\Landing\Member\Infrastructure\Support;

use Src\Admin\Team\Infrastructure\Models\TeamEloquentModel;

final class MemberPresenter
{
    public static function present(TeamEloquentModel $team, string $lang): array
    {
        $translation = $team->translations->firstWhere('lang', $lang)
            ?? $team->translations->first();

        $results = $team->images->sortBy('order')->map(function ($img) use ($lang) {
            $t = $img->translations->firstWhere('lang', $lang)
                ?? $img->translations->first();

            return [
                'id'          => $img->id,
                'path'        => $img->path,
                'order'       => (int) ($img->order ?? 0),
                'description' => $t?->description ?? '',
            ];
        })->values()->toArray();

        return [
            'id'             => $team->id,
            'slug'           => $team->slug ?? '',
            'name'           => $team->name ?? '',
            'status'         => $team->status ?? '',
            'image'          => $team->image ?? '',
            'biography'      => $translation?->biography ?? '',
            'description'    => $translation?->description ?? '',
            'specialization' => $translation?->specialization ?? '',
            'results'        => $results,
        ];
    }
}
