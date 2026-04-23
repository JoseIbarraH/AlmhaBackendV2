<?php

declare(strict_types=1);

namespace Src\Landing\Maintenance\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Src\Admin\Settings\Infrastructure\Models\EloquentSettingModel;
use Src\Shared\Infrastructure\Cache\ClientCache;
use Src\Shared\Infrastructure\Http\ClientResponse;

final class GetMaintenanceController
{
    #[OA\Get(
        path: "/api/client/maintenance",
        summary: "Flag de mantenimiento del sitio público",
        description: "Devuelve si el sitio está en modo mantenimiento. Cacheado 60s server-side y con Cache-Control al cliente.",
        tags: ["Client / Maintenance"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Estado del flag",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "success", type: "boolean"),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "key", type: "string", example: "is_maintenance_mode"),
                            new OA\Property(property: "value", type: "boolean"),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(): JsonResponse
    {
        $payload = ClientCache::remember(
            'maintenance',
            'maintenance',
            ClientCache::TTL_SHORT,
            function (): array {
                $setting = EloquentSettingModel::where('key', 'is_maintenance_mode')->first();
                return [
                    'key'   => 'is_maintenance_mode',
                    'value' => (bool) ($setting?->value ?? false),
                ];
            }
        );

        // Cache-Control: lets CDN/proxies/Astro serve this for 60s without round-tripping.
        return ClientResponse::success($payload, '', 200, maxAgeSeconds: 60);
    }
}
