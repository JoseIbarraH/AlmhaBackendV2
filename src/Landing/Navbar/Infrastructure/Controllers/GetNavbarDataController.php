<?php

declare(strict_types=1);

namespace Src\Landing\Navbar\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureEloquentModel;
use Src\Admin\Settings\Infrastructure\Models\EloquentSettingModel;
use Src\Landing\Navbar\Infrastructure\Support\DesignItemPresenter;
use Src\Landing\Procedure\Infrastructure\Support\ProcedurePresenter;
use Src\Shared\Infrastructure\Cache\ClientCache;
use Src\Shared\Infrastructure\Support\MediaUrl;
use Src\Shared\Infrastructure\Http\ClientResponse;
use Src\Shared\Infrastructure\Http\ResolvesLanguage;

final class GetNavbarDataController
{
    use ResolvesLanguage;

    #[OA\Get(
        path: "/api/client/navbar-data",
        summary: "Datos del navbar del sitio público",
        description: "Incluye carrusel principal, procedimientos agrupados por categoría, top 4 por views y settings de contacto/redes. Cacheado 5 min.",
        tags: ["Client / Navbar"],
        parameters: [
            new OA\Parameter(name: "Accept-Language", in: "header", required: false, schema: new OA\Schema(type: "string", default: "es")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Datos del navbar"),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $lang = $this->resolveLang($request);

        $payload = ClientCache::remember(
            'navbar',
            "navbar:{$lang}",
            ClientCache::TTL_MEDIUM,
            fn () => $this->buildPayload($lang)
        );

        return ClientResponse::success($payload, '', 200, maxAgeSeconds: 300);
    }

    private function buildPayload(string $lang): array
    {
        $carousel = DesignItemPresenter::itemsFor('main_banner', $lang);

        $procedures = ProcedureEloquentModel::query()
            ->where('status', 'published')
            ->with(['translations' => fn ($q) => $q->where('lang', $lang)])
            ->orderBy('category_code')
            ->get();

        $grouped = [];
        foreach ($procedures as $p) {
            $t = $p->translations->first();
            $categoryTitle = ProcedurePresenter::categoryTitle($p->category_code, $lang);
            $key = $categoryTitle !== '' ? $categoryTitle : ($p->category_code ?? 'otros');

            $grouped[$key][] = [
                'id'       => $p->id,
                'image'    => MediaUrl::resolve($p->image),
                'slug'     => $t?->slug ?? '',
                'title'    => $t?->title ?? '',
                'category' => $categoryTitle,
            ];
        }

        $topProcedure = ProcedureEloquentModel::query()
            ->where('status', 'published')
            ->with(['translations' => fn ($q) => $q->where('lang', $lang)])
            ->orderByDesc('views')
            ->limit(4)
            ->get()
            ->map(function ($p) use ($lang) {
                $t = $p->translations->first();
                return [
                    'id'       => $p->id,
                    'image'    => MediaUrl::resolve($p->image),
                    'slug'     => $t?->slug ?? '',
                    'title'    => $t?->title ?? '',
                    'category' => ProcedurePresenter::categoryTitle($p->category_code, $lang),
                ];
            })
            ->values()
            ->toArray();

        $social   = EloquentSettingModel::where('group', 'social')->get()->pluck('value', 'key');
        $general  = EloquentSettingModel::where('group', 'general')->get()->pluck('value', 'key');
        $whatsapp = is_array($general['whatsapp'] ?? null) ? $general['whatsapp'] : [];

        $settings = [
            'social' => [
                'social_facebook'  => $social['facebook']  ?? '',
                'social_instagram' => $social['instagram'] ?? '',
                'social_threads'   => $social['threads']   ?? '',
                'social_twitter'   => $social['twitter']   ?? '',
                'social_linkedin'  => $social['linkedin']  ?? '',
            ],
            'contact' => array_filter([
                'contact_phone'    => $general['phone']    ?? '',
                'contact_email'    => $general['email']    ?? '',
                'contact_location' => $general['location'] ?? '',
                'whatsapp' => !empty($whatsapp) ? [
                    'phone'           => $whatsapp['number']      ?? '',
                    'default_message' => $whatsapp['message']     ?? '',
                    'is_active'       => (bool) ($whatsapp['show_button']  ?? false),
                    'open_in_new_tab' => (bool) ($whatsapp['open_new_tab'] ?? false),
                ] : null,
            ], fn ($v) => $v !== null && $v !== ''),
        ];

        return [
            'carousel'     => $carousel,
            'procedures'   => $grouped,
            'topProcedure' => $topProcedure,
            'settings'     => $settings,
        ];
    }
}
