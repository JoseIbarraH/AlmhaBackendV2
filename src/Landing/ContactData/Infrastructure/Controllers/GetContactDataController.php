<?php

declare(strict_types=1);

namespace Src\Landing\ContactData\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureEloquentModel;
use Src\Admin\Settings\Infrastructure\Models\EloquentSettingModel;
use Src\Shared\Infrastructure\Cache\ClientCache;
use Src\Shared\Infrastructure\Http\ClientResponse;
use Src\Shared\Infrastructure\Http\ResolvesLanguage;

final class GetContactDataController
{
    use ResolvesLanguage;

    #[OA\Get(
        path: "/api/client/contact-data",
        summary: "Datos para la página de contacto",
        description: "Devuelve settings de contacto, WhatsApp y lista de títulos de procedimientos para el combobox del form.",
        tags: ["Client / Contact"],
        parameters: [
            new OA\Parameter(name: "Accept-Language", in: "header", required: false, schema: new OA\Schema(type: "string", default: "es")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Settings + lista de procedimientos"),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $lang = $this->resolveLang($request);

        $payload = ClientCache::remember(
            'contact_data',
            "contact_data:{$lang}",
            ClientCache::TTL_LONG,
            fn () => $this->buildPayload($lang)
        );

        return ClientResponse::success($payload);
    }

    private function buildPayload(string $lang): array
    {
        $general  = EloquentSettingModel::where('group', 'general')->get()->pluck('value', 'key');
        $whatsapp = is_array($general['whatsapp'] ?? null) ? $general['whatsapp'] : [];

        $settings = [
            'phone'                 => $general['phone']    ?? null,
            'email'                 => $general['email']    ?? null,
            'location'              => $general['location'] ?? null,
            'whatsapp_number'       => $whatsapp['number']       ?? null,
            'whatsapp_active'       => (bool) ($whatsapp['show_button']  ?? false),
            'whatsapp_message'      => $whatsapp['message']      ?? null,
            'whatsapp_open_new_tab' => (bool) ($whatsapp['open_new_tab'] ?? false),
        ];

        $procedures = ProcedureEloquentModel::query()
            ->where('status', 'published')
            ->with(['translations' => fn ($q) => $q->where('lang', $lang)])
            ->get()
            ->map(function ($p) {
                $title = $p->translations->first()?->title ?? '';
                return ['title' => $title];
            })
            ->filter(fn ($item) => $item['title'] !== '')
            ->values()
            ->toArray();

        return [
            'settings'   => $settings,
            'procedures' => $procedures,
        ];
    }
}
