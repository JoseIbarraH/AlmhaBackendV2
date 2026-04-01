<?php

namespace Src\Admin\Design\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Design\Application\UpdateDesignModeUseCase;

class UpdateDesignModeController
{
    private UpdateDesignModeUseCase $useCase;

    public function __construct(UpdateDesignModeUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request, int $designId): JsonResponse
    {
        $validated = $request->validate([
            'display_mode' => 'required|string|in:single_image,carousel,video'
        ]);

        $this->useCase->execute($designId, $validated['display_mode']);

        return response()->json([
            'success' => true,
            'message' => 'Design mode updated successfully'
        ]);
    }
}
