<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\DeleteProcedureCategoryUseCase;
use Exception;

final class DeleteProcedureCategoryController
{
    private DeleteProcedureCategoryUseCase $useCase;

    public function __construct(DeleteProcedureCategoryUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request, int $id): JsonResponse
    {
        try {
            $this->useCase->execute($id);

            return response()->json([
                'message' => 'Procedure category deleted successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
