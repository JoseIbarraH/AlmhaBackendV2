<?php

declare(strict_types=1);

namespace Src\Landing\Maintenance\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Settings\Infrastructure\Models\EloquentSettingModel;
use Src\Shared\Infrastructure\Cache\ClientCache;
use Src\Shared\Infrastructure\Http\ClientResponse;

final class GetMaintenanceController
{
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
