<?php

declare(strict_types=1);

namespace Src\Landing\Blog\Infrastructure\Support;

use Src\Admin\Blog\Infrastructure\Models\BlogCategoryEloquentModel;
use Src\Admin\Blog\Infrastructure\Models\BlogEloquentModel;

final class BlogPresenter
{
    public static function listItem(BlogEloquentModel $blog, string $lang): array
    {
        $t = self::pickTranslation($blog, $lang);
        $categoryTitle = self::categoryTitle($blog->category_code, $lang);

        return [
            'id'            => $blog->id,
            'title'         => $t['title'] ?? '',
            'slug'          => $t['slug'] ?? '',
            'image'         => $blog->image ?? '',
            'status'        => $blog->status ?? '',
            'writer'        => $blog->writer ?? '',
            'excerpts'      => self::excerpt($t['content'] ?? ''),
            'category'      => $categoryTitle,
            'category_code' => $blog->category_code ?? '',
            'created_at'    => optional($blog->created_at)->toDateTimeString() ?? '',
        ];
    }

    public static function detail(BlogEloquentModel $blog, string $lang): array
    {
        $t = self::pickTranslation($blog, $lang);
        $categoryTitle = self::categoryTitle($blog->category_code, $lang);

        return [
            'id'            => $blog->id,
            'slug'          => $t['slug'] ?? '',
            'image'         => $blog->image ?? '',
            'writer'        => $blog->writer ?? '',
            'title'         => $t['title'] ?? '',
            'content'       => $t['content'] ?? '',
            'category'      => $categoryTitle,
            'category_code' => $blog->category_code ?? '',
            'status'        => $blog->status ?? '',
            'created_at'    => optional($blog->created_at)->toDateTimeString() ?? '',
        ];
    }

    /** @return array{title:?string,content:?string,slug:?string} */
    private static function pickTranslation(BlogEloquentModel $blog, string $lang): array
    {
        $found = $blog->translations->firstWhere('lang', $lang)
            ?? $blog->translations->first();

        return [
            'title'   => $found?->title,
            'content' => $found?->content,
            'slug'    => $found?->slug,
        ];
    }

    private static function categoryTitle(?string $code, string $lang): string
    {
        if (!$code) {
            return '';
        }

        $cat = BlogCategoryEloquentModel::with('translations')
            ->where('code', $code)
            ->first();

        if (!$cat) {
            return $code;
        }

        $t = $cat->translations->firstWhere('lang', $lang)
            ?? $cat->translations->first();

        return $t?->title ?? $code;
    }

    private static function excerpt(string $html, int $length = 160): string
    {
        $plain = trim(strip_tags($html));
        if (mb_strlen($plain) <= $length) {
            return $plain;
        }
        return mb_substr($plain, 0, $length) . '…';
    }
}
