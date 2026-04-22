<?php

declare(strict_types=1);

namespace Src\Landing\Procedure\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
