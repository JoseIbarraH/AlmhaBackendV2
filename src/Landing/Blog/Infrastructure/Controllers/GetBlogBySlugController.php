<?php

declare(strict_types=1);

namespace Src\Landing\Blog\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Src\Admin\Blog\Infrastructure\Models\BlogEloquentModel;
use Src\Admin\Blog\Infrastructure\Models\BlogTranslationEloquentModel;
use Src\Landing\Blog\Infrastructure\Support\BlogPresenter;
use Src\Shared\Infrastructure\Cache\ClientCache;
use Src\Shared\Infrastructure\Http\ClientResponse;
use Src\Shared\Infrastructure\Http\ResolvesLanguage;

final class GetBlogBySlugController
{
    use ResolvesLanguage;

    #[OA\Get(
        path: "/api/client/blog/{slug}",
        summary: "Detalle público de un blog por slug",
        description: "Incrementa el contador de views. El payload incluye random_blogs.",
        tags: ["Client / Blog"],
        parameters: [
            new OA\Parameter(name: "slug", in: "path", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "Accept-Language", in: "header", required: false, schema: new OA\Schema(type: "string", default: "es")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Blog + random_blogs"),
            new OA\Response(response: 404, description: "No encontrado"),
        ]
    )]
    public function __invoke(Request $request, string $slug): JsonResponse
    {
        $lang = $this->resolveLang($request);

        // Lookup is cheap (indexed); we keep it outside cache so 404s are correct
        // even when the cache hasn't seen this slug yet.
        $translation = BlogTranslationEloquentModel::where('slug', $slug)
            ->where('lang', $lang)
            ->first();

        if (!$translation) {
            return ClientResponse::error('Blog not found.', 404);
        }

        $blog = BlogEloquentModel::where('status', 'published')->find($translation->blog_id);

        if (!$blog) {
            return ClientResponse::error('Blog not found.', 404);
        }

        // Always increment views — this is a side-effect that must not be cached.
        $blog->increment('views');

        // Cache the heavy presenter work. random_blogs would change per call, so
        // keeping it inside the cache is acceptable (refreshed every TTL window).
        $payload = ClientCache::remember(
            'blog',
            "blog:detail:{$lang}:{$blog->id}",
            ClientCache::TTL_MEDIUM,
            function () use ($blog, $lang) {
                $blog->load(['translations' => fn ($q) => $q->where('lang', $lang)]);

                $randomBlogs = BlogEloquentModel::query()
                    ->where('status', 'published')
                    ->where('id', '!=', $blog->id)
                    ->with(['translations' => fn ($q) => $q->where('lang', $lang)])
                    ->inRandomOrder()
                    ->limit(3)
                    ->get()
                    ->map(fn (BlogEloquentModel $b) => BlogPresenter::detail($b, $lang))
                    ->values()
                    ->toArray();

                $detail = BlogPresenter::detail($blog, $lang);
                $detail['random_blogs'] = $randomBlogs;

                return $detail;
            }
        );

        return ClientResponse::success($payload);
    }
}
