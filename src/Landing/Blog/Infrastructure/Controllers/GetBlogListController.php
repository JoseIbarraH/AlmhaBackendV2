<?php

declare(strict_types=1);

namespace Src\Landing\Blog\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Blog\Infrastructure\Models\BlogCategoryEloquentModel;
use Src\Admin\Blog\Infrastructure\Models\BlogEloquentModel;
use Src\Landing\Blog\Infrastructure\Support\BlogPresenter;
use Src\Shared\Infrastructure\Cache\ClientCache;
use Src\Shared\Infrastructure\Http\ClientResponse;
use Src\Shared\Infrastructure\Http\ResolvesLanguage;

final class GetBlogListController
{
    use ResolvesLanguage;

    public function __invoke(Request $request): JsonResponse
    {
        $lang     = $this->resolveLang($request);
        $page     = max(1, (int) $request->query('page', 1));
        $perPage  = min(50, max(1, (int) $request->query('per_page', 9)));
        $category = (string) ($request->input('filter.category_code') ?? '');
        $search   = (string) ($request->input('filter.search') ?? '');
        $sort     = (string) $request->query('sort', '-published_at');

        $cacheKey = sprintf(
            'blog:list:%s:p%d:pp%d:%s',
            $lang,
            $page,
            $perPage,
            md5("{$category}|{$search}|{$sort}")
        );

        $payload = ClientCache::remember(
            'blog',
            $cacheKey,
            ClientCache::TTL_MEDIUM,
            fn () => $this->buildList($lang, $page, $perPage, $category, $search, $sort)
        );

        return ClientResponse::success($payload);
    }

    private function buildList(string $lang, int $page, int $perPage, string $category, string $search, string $sort): array
    {
        [$sortColumn, $sortDir] = $this->parseSort($sort);

        $query = BlogEloquentModel::query()
            ->where('status', 'published')
            ->with(['translations' => fn ($q) => $q->where('lang', $lang)])
            ->when($category, fn ($q, $c) => $q->where('category_code', $c))
            ->when($search, function ($q, $s) use ($lang) {
                $q->whereHas('translations', function ($tq) use ($s, $lang) {
                    $tq->where('lang', $lang)
                        ->where(function ($qq) use ($s) {
                            $qq->where('title', 'like', "%{$s}%")
                                ->orWhere('content', 'like', "%{$s}%");
                        });
                });
            })
            ->orderBy($sortColumn, $sortDir);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Collections that end up cached need `->values()->all()` so that after
        // PHP serialize → unserialize → json_encode the result is a JSON array,
        // not an object with numeric string keys.
        $items = collect($paginator->items())
            ->map(fn ($blog) => BlogPresenter::listItem($blog, $lang))
            ->values()
            ->all();

        $pagination = array_merge($paginator->toArray(), ['data' => $items]);

        $lastThree = BlogEloquentModel::query()
            ->where('status', 'published')
            ->with(['translations' => fn ($q) => $q->where('lang', $lang)])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get()
            ->map(fn ($blog) => BlogPresenter::listItem($blog, $lang))
            ->values()
            ->all();

        $categories = $this->buildCategories($lang);

        return [
            'filters'    => ['search' => $search],
            'pagination' => $pagination,
            'categories' => $categories,
            'last_three' => $lastThree,
        ];
    }

    /** @return array{0:string,1:string} */
    private function parseSort(string $sort): array
    {
        $allowed = ['published_at', 'created_at', 'views'];
        $dir = 'desc';
        if (str_starts_with($sort, '-')) {
            $sort = substr($sort, 1);
        } elseif (str_starts_with($sort, '+')) {
            $sort = substr($sort, 1);
            $dir  = 'asc';
        } else {
            $dir = 'asc';
        }

        if (!in_array($sort, $allowed, true)) {
            return ['published_at', 'desc'];
        }

        return [$sort, $dir];
    }

    private function buildCategories(string $lang): array
    {
        $categories = BlogCategoryEloquentModel::query()
            ->with(['translations' => fn ($q) => $q->where('lang', $lang)])
            ->get();

        return $categories->map(function ($cat) use ($lang) {
            $title = $cat->translations->firstWhere('lang', $lang)?->title
                ?? $cat->translations->first()?->title
                ?? $cat->code;

            $count = BlogEloquentModel::where('status', 'published')
                ->where('category_code', $cat->code)
                ->count();

            return [
                'code'  => $cat->code,
                'title' => $title,
                'count' => $count,
            ];
        })->values()->toArray();
    }
}
