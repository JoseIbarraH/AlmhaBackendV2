<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Blog\Application\ChangeBlogStatusUseCase;
use Exception;

final class ChangeBlogStatusController
{
    private ChangeBlogStatusUseCase $useCase;

    public function __construct(ChangeBlogStatusUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:draft,published,archived'
        ]);

        try {
            $this->useCase->execute($id, $request->input('status'));

            return response()->json([
                'message' => 'Blog status updated successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
