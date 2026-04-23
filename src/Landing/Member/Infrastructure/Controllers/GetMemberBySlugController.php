<?php

declare(strict_types=1);

namespace Src\Landing\Member\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Src\Admin\Team\Infrastructure\Models\TeamEloquentModel;
use Src\Landing\Member\Infrastructure\Support\MemberPresenter;
use Src\Shared\Infrastructure\Cache\ClientCache;
use Src\Shared\Infrastructure\Http\ClientResponse;
use Src\Shared\Infrastructure\Http\ResolvesLanguage;

final class GetMemberBySlugController
{
    use ResolvesLanguage;

    #[OA\Get(
        path: "/api/client/members/{slug}",
        summary: "Detalle público de un miembro del equipo",
        tags: ["Client / Member"],
        parameters: [
            new OA\Parameter(name: "slug", in: "path", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "Accept-Language", in: "header", required: false, schema: new OA\Schema(type: "string", default: "es")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Miembro con biografía, especialización y galería"),
            new OA\Response(response: 404, description: "No encontrado"),
        ]
    )]
    public function __invoke(Request $request, string $slug): JsonResponse
    {
        $lang = $this->resolveLang($request);

        $payload = ClientCache::remember(
            'member',
            "member:detail:{$lang}:{$slug}",
            ClientCache::TTL_LONG,
            function () use ($lang, $slug) {
                $team = TeamEloquentModel::query()
                    ->where('slug', $slug)
                    ->where('status', 'active')
                    ->with([
                        'translations' => fn ($q) => $q->where('lang', $lang),
                        'images.translations' => fn ($q) => $q->where('lang', $lang),
                    ])
                    ->first();

                return $team ? MemberPresenter::present($team, $lang) : null;
            }
        );

        if ($payload === null) {
            return ClientResponse::error('Member not found.', 404);
        }

        return ClientResponse::success($payload);
    }
}
