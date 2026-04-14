<?php

namespace Src\Admin\Design\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Design\Application\UpdateDesignStatusUseCase;

class UpdateDesignStatusController
{
    private UpdateDesignStatusUseCase $useCase;

    public function __construct(UpdateDesignStatusUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request, int $designId): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:active,inactive'
        ]);

        $this->useCase->execute($designId, $validated['status']);

        return response()->json([
            'success' => true,
            'message' => 'Design status updated successfully'
        ]);
    }
}
