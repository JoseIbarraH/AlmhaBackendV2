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

final class GetMemberListController
{
    use ResolvesLanguage;

    #[OA\Get(
        path: "/api/client/members",
        summary: "Listado público del equipo",
        description: "Solo miembros con status=active. Incluye galería de resultados de cada uno.",
        tags: ["Client / Member"],
        parameters: [
            new OA\Parameter(name: "Accept-Language", in: "header", required: false, schema: new OA\Schema(type: "string", default: "es")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Array de miembros con sus imágenes"),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $lang = $this->resolveLang($request);

        $members = ClientCache::remember(
            'member',
            "member:list:{$lang}",
            ClientCache::TTL_LONG,
            function () use ($lang) {
                return TeamEloquentModel::query()
                    ->where('status', 'active')
                    ->with([
                        'translations' => fn ($q) => $q->where('lang', $lang),
                        'images.translations' => fn ($q) => $q->where('lang', $lang),
                    ])
                    ->orderBy('id')
                    ->get()
                    ->map(fn (TeamEloquentModel $t) => MemberPresenter::present($t, $lang))
                    ->values()
                    ->toArray();
            }
        );

        return ClientResponse::success($members);
    }
}
