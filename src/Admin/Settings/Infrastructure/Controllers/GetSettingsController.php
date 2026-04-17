<?php

namespace Src\Admin\Settings\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Settings\Application\GetSettingsByGroupUseCase;

class GetSettingsController
{
    public function __construct(
        private GetSettingsByGroupUseCase $useCase
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $group = $request->query('group', 'general');
        $settings = $this->useCase->execute($group);

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }
}
