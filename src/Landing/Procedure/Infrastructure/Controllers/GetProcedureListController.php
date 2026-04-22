<?php

declare(strict_types=1);

namespace Src\Landing\Procedure\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureCategoryEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureEloquentModel;
use Src\Landing\Procedure\Infrastructure\Support\ProcedurePresenter;
use Src\Shared\Infrastructure\Cache\ClientCache;
use Src\Shared\Infrastructure\Http\ClientResponse;
use Src\Shared\Infrastructure\Http\ResolvesLanguage;

final class GetProcedureListController
{
    use ResolvesLanguage;

    public function __invoke(Request $request): JsonResponse
    {
        $lang     = $this->resolveLang($request);
        $page     = max(1, (int) $request->query('page', 1));
        $perPage  = min(50, max(1, (int) $request->query('per_page', 9)));
        $category = (string) ($request->input('filter.category_code') ?? '');
        $search   = (string) ($request->input('filter.search') ?? '');
        $sort     = (string) $request->query('sort', '-created_at');

        $cacheKey = sprintf(
            'procedure:list:%s:p%d:pp%d:%s',
            $lang,
            $page,
            $perPage,
            md5("{$category}|{$search}|{$sort}")
        );

        $payload = ClientCache::remember(
            'procedure',
            $cacheKey,
            ClientCache::TTL_MEDIUM,
            fn () => $this->buildList($lang, $page, $perPage, $category, $search, $sort)
        );

        return ClientResponse::success($payload);
    }

    private function buildList(string $lang, int $page, int $perPage, string $category, string $search, string $sort): array
    {
        [$sortColumn, $sortDir] = $this->parseSort($sort);

        $query = ProcedureEloquentModel::query()
            ->where('status', 'published')
            ->with(['translations' => fn ($q) => $q->where('lang', $lang)])
            ->when($category, fn ($q, $c) => $q->where('category_code', $c))
            ->when($search, function ($q, $s) use ($lang) {
                $q->whereHas('translations', function ($tq) use ($s, $lang) {
                    $tq->where('lang', $lang)
                        ->where(function ($qq) use ($s) {
                            $qq->where('title', 'like', "%{$s}%")
                                ->orWhere('subtitle', 'like', "%{$s}%");
                        });
                });
            })
            ->orderBy($sortColumn, $sortDir);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Ensure plain array (not Collection) — safer for JSON round-trips
        // through the cache serialize/unserialize pipeline.
        $items = collect($paginator->items())
            ->map(fn ($p) => ProcedurePresenter::listItem($p, $lang))
            ->values()
            ->all();

        $pagination = array_merge($paginator->toArray(), ['data' => $items]);

        $categories = $this->buildCategories($lang);

        return [
            'filters'    => ['search' => $search],
            'pagination' => $pagination,
            'categories' => $categories,
        ];
    }

    /** @return array{0:string,1:string} */
    private function parseSort(string $sort): array
    {
        $allowed = ['created_at', 'views'];
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
            return ['created_at', 'desc'];
        }

        return [$sort, $dir];
    }

    private function buildCategories(string $lang): array
    {
        $categories = ProcedureCategoryEloquentModel::query()
            ->with(['translations' => fn ($q) => $q->where('lang', $lang)])
            ->get();

        return $categories->map(function ($cat) use ($lang) {
            $title = $cat->translations->firstWhere('lang', $lang)?->title
                ?? $cat->translations->first()?->title
                ?? $cat->code;

            $count = ProcedureEloquentModel::where('status', 'published')
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
