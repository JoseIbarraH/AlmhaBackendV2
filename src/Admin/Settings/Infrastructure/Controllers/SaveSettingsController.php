<?php

namespace Src\Admin\Settings\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Settings\Application\SaveSettingsUseCase;

class SaveSettingsController
{
    public function __construct(
        private SaveSettingsUseCase $useCase
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $group = $request->input('group', 'general');
        $settings = $request->input('settings', []);

        if (empty($settings)) {
            return response()->json([
                'success' => false,
                'message' => 'No settings provided'
            ], 400);
        }

        $this->useCase->execute($settings, $group);

        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully'
        ]);
    }
}
