<?php

declare(strict_types=1);

namespace Src\Landing\Member\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Team\Infrastructure\Models\TeamEloquentModel;
use Src\Landing\Member\Infrastructure\Support\MemberPresenter;
use Src\Shared\Infrastructure\Cache\ClientCache;
use Src\Shared\Infrastructure\Http\ClientResponse;
use Src\Shared\Infrastructure\Http\ResolvesLanguage;

final class GetMemberListController
{
    use ResolvesLanguage;

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
