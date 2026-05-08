<?php

declare(strict_types=1);

namespace Src\Landing\About\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Src\Admin\Settings\Infrastructure\Models\EloquentSettingModel;
use Src\Shared\Infrastructure\Cache\ClientCache;
use Src\Shared\Infrastructure\Http\ClientResponse;
use Src\Shared\Infrastructure\Http\ResolvesLanguage;

final class GetAboutController
{
    use ResolvesLanguage;

    #[OA\Get(
        path: "/api/client/about",
        summary: "Datos editables de la página Sobre Nosotros (misión y visión)",
        description: "Devuelve la misión y visión almacenadas en settings, en el idioma resuelto desde ?lang= o Accept-Language. Cae a 'es' si la traducción solicitada no existe.",
        tags: ["Client / About"],
        parameters: [
            new OA\Parameter(name: "lang", in: "query", required: false, schema: new OA\Schema(type: "string", enum: ["es", "en", "fr"])),
            new OA\Parameter(name: "Accept-Language", in: "header", required: false, schema: new OA\Schema(type: "string", default: "es")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Misión y visión en el idioma solicitado"),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $lang = $this->resolveLang($request);

        $payload = ClientCache::remember(
            'about',
            "about:{$lang}",
            ClientCache::TTL_SHORT,
            fn () => $this->buildPayload($lang)
        );

        return ClientResponse::success($payload);
    }

    private function buildPayload(string $lang): array
    {
        $settings = EloquentSettingModel::where('group', 'general')
            ->whereIn('key', ['about_mission', 'about_vision'])
            ->get()
            ->pluck('value', 'key');

        return [
            'mission' => $this->extractTranslation($settings['about_mission'] ?? null, $lang),
            'vision'  => $this->extractTranslation($settings['about_vision']  ?? null, $lang),
        ];
    }

    private function extractTranslation(mixed $value, string $lang): ?array
    {
        if (!\is_array($value)) {
            return null;
        }

        $translation = $this->pickNonEmpty($value, [$lang, 'es', 'en']);
        if ($translation === null) {
            return null;
        }

        return [
            'title'       => $this->decodeEntities((string) ($translation['title'] ?? '')),
            'description' => $this->decodeEntities((string) ($translation['description'] ?? '')),
        ];
    }

    /**
     * Walk through preferred languages and return the first one that actually has content.
     */
    private function pickNonEmpty(array $value, array $langs): ?array
    {
        foreach ($langs as $lang) {
            $candidate = $value[$lang] ?? null;
            if (!\is_array($candidate)) {
                continue;
            }
            $title       = trim((string) ($candidate['title'] ?? ''));
            $description = trim((string) ($candidate['description'] ?? ''));
            if ($title !== '' || $description !== '') {
                return $candidate;
            }
        }
        return null;
    }

    /**
     * Defensive decode for legacy rows saved before HTML-entity decoding was added on save.
     */
    private function decodeEntities(string $text): string
    {
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
