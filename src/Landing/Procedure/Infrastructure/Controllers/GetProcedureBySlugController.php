<?php

declare(strict_types=1);

namespace Src\Landing\Procedure\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureTranslationEloquentModel;
use Src\Admin\Settings\Infrastructure\Models\EloquentSettingModel;
use Src\Landing\Procedure\Infrastructure\Support\ProcedurePresenter;
use Src\Shared\Infrastructure\Cache\ClientCache;
use Src\Shared\Infrastructure\Http\ClientResponse;
use Src\Shared\Infrastructure\Http\ResolvesLanguage;

final class GetProcedureBySlugController
{
    use ResolvesLanguage;

    #[OA\Get(
        path: "/api/client/procedure/{slug}",
        summary: "Detalle público de un procedimiento por slug",
        description: "Incluye secciones, FAQs, pasos de preparación, fases de recuperación, galería y datos de WhatsApp.",
        tags: ["Client / Procedure"],
        parameters: [
            new OA\Parameter(name: "slug", in: "path", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "Accept-Language", in: "header", required: false, schema: new OA\Schema(type: "string", default: "es")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Procedimiento completo"),
            new OA\Response(response: 404, description: "No encontrado"),
        ]
    )]
    public function __invoke(Request $request, string $slug): JsonResponse
    {
        $lang = $this->resolveLang($request);

        $translation = ProcedureTranslationEloquentModel::where('slug', $slug)
            ->where('lang', $lang)
            ->first();

        if (!$translation) {
            return ClientResponse::error('Procedure not found.', 404);
        }

        $procedure = ProcedureEloquentModel::where('status', 'published')
            ->find($translation->procedure_id);

        if (!$procedure) {
            return ClientResponse::error('Procedure not found.', 404);
        }

        // Always increment views — side-effect must not be cached.
        $procedure->increment('views');

        $payload = ClientCache::remember(
            'procedure',
            "procedure:detail:{$lang}:{$procedure->id}",
            ClientCache::TTL_MEDIUM,
            function () use ($procedure, $lang) {
                $detail = ProcedurePresenter::detail($procedure, $lang);

                $general  = EloquentSettingModel::where('group', 'general')->get()->pluck('value', 'key');
                $whatsapp = \is_array($general['whatsapp'] ?? null) ? $general['whatsapp'] : [];

                $detail['whatsapp_number']       = $whatsapp['number']        ?? null;
                $detail['whatsapp_active']       = (bool) ($whatsapp['show_button']  ?? false);
                $detail['whatsapp_message']      = $whatsapp['message']       ?? null;
                $detail['whatsapp_open_new_tab'] = (bool) ($whatsapp['open_new_tab'] ?? false);

                return $detail;
            }
        );

        return ClientResponse::success($payload);
    }
}
