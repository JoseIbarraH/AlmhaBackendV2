<?php

declare(strict_types=1);

namespace Src\Landing\Procedure\Infrastructure\Support;

use Src\Admin\Procedure\Infrastructure\Models\ProcedureCategoryEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureEloquentModel;
use Src\Shared\Infrastructure\Support\MediaUrl;

final class ProcedurePresenter
{
    public static function listItem(ProcedureEloquentModel $p, string $lang): array
    {
        $t = self::pickTranslation($p, $lang);

        return [
            'id'            => $p->id,
            'status'        => $p->status ?? '',
            'slug'          => $t['slug'] ?? '',
            'image'         => MediaUrl::resolve($p->image),
            'title'         => $t['title'] ?? '',
            'subtitle'      => $t['subtitle'] ?? '',
            'category'      => self::categoryTitle($p->category_code, $lang),
            'category_code' => $p->category_code ?? '',
            'created_at'    => optional($p->created_at)->toDateTimeString() ?? '',
        ];
    }

    public static function detail(ProcedureEloquentModel $p, string $lang): array
    {
        $p->loadMissing([
            'translations'              => fn ($q) => $q->where('lang', $lang),
            'sections.translations'     => fn ($q) => $q->where('lang', $lang),
            'faqs.translations'         => fn ($q) => $q->where('lang', $lang),
            'postoperativeInstructions.translations' => fn ($q) => $q->where('lang', $lang),
            'preparationSteps.translations' => fn ($q) => $q->where('lang', $lang),
            'recoveryPhases.translations'   => fn ($q) => $q->where('lang', $lang),
            'gallery',
        ]);

        $t = self::pickTranslation($p, $lang);

        $sections = $p->sections->map(fn ($s) => [
            'id'         => $s->id,
            'type'       => $s->type,
            'image'      => MediaUrl::resolve($s->image),
            'title'      => $s->translations->first()?->title ?? '',
            'contentOne' => $s->translations->first()?->content_one ?? '',
            'contentTwo' => $s->translations->first()?->content_two ?? '',
        ])->values()->toArray();

        $preStep = $p->preparationSteps->sortBy('order')->map(fn ($s) => [
            'id'          => $s->id,
            'title'       => $s->translations->first()?->title ?? '',
            'description' => $s->translations->first()?->description ?? '',
            'order'       => $s->order,
        ])->values()->toArray();

        $phases = $p->recoveryPhases->sortBy('order')->map(fn ($ph) => [
            'id'          => $ph->id,
            'period'      => $ph->translations->first()?->period ?? '',
            'title'       => $ph->translations->first()?->title ?? '',
            'description' => $ph->translations->first()?->description ?? '',
            'order'       => $ph->order,
        ])->values()->toArray();

        $doList = $p->postoperativeInstructions
            ->where('type', 'do')
            ->sortBy('order')
            ->map(fn ($i) => [
                'id'      => $i->id,
                'type'    => 'do',
                'order'   => $i->order,
                'content' => $i->translations->first()?->content ?? '',
            ])->values()->toArray();

        $dontList = $p->postoperativeInstructions
            ->where('type', 'dont')
            ->sortBy('order')
            ->map(fn ($i) => [
                'id'      => $i->id,
                'type'    => 'dont',
                'order'   => $i->order,
                'content' => $i->translations->first()?->content ?? '',
            ])->values()->toArray();

        $faqs = $p->faqs->sortBy('order')->map(fn ($f) => [
            'id'       => $f->id,
            'question' => $f->translations->first()?->question ?? '',
            'answer'   => $f->translations->first()?->answer ?? '',
            'order'    => $f->order,
        ])->values()->toArray();

        $gallery = $p->gallery->sortBy('order')->map(fn ($g) => [
            'id'    => $g->id,
            'path'  => MediaUrl::resolve($g->path),
            'order' => $g->order,
        ])->values()->toArray();

        return [
            'id'            => $p->id,
            'status'        => $p->status ?? '',
            'slug'          => $t['slug'] ?? '',
            'image'         => MediaUrl::resolve($p->image),
            'views'         => (int) ($p->views ?? 0),
            'title'         => $t['title'] ?? '',
            'subtitle'      => $t['subtitle'] ?? '',
            'category'      => self::categoryTitle($p->category_code, $lang),
            'category_code' => $p->category_code ?? '',
            'created_at'    => optional($p->created_at)->toDateTimeString() ?? '',
            'section'       => $sections,
            'preStep'       => $preStep,
            'phase'         => $phases,
            'do'            => $doList,
            'dont'          => $dontList,
            'faq'           => $faqs,
            'gallery'       => $gallery,
        ];
    }

    /** @return array{title:?string,subtitle:?string,slug:?string} */
    private static function pickTranslation(ProcedureEloquentModel $p, string $lang): array
    {
        $found = $p->translations->firstWhere('lang', $lang)
            ?? $p->translations->first();

        return [
            'title'    => $found?->title,
            'subtitle' => $found?->subtitle,
            'slug'     => $found?->slug,
        ];
    }

    public static function categoryTitle(?string $code, string $lang): string
    {
        if (!$code) {
            return '';
        }

        $cat = ProcedureCategoryEloquentModel::with('translations')
            ->where('code', $code)
            ->first();

        if (!$cat) {
            return $code;
        }

        $t = $cat->translations->firstWhere('lang', $lang)
            ?? $cat->translations->first();

        return $t?->title ?? $code;
    }
}
