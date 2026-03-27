<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\GetAllProcedureCategoriesUseCase;
use Exception;

final class GetAllProcedureCategoriesController
{
    private GetAllProcedureCategoriesUseCase $useCase;

    public function __construct(GetAllProcedureCategoriesUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(): JsonResponse
    {
        try {
            $categories = $this->useCase->execute();
            
            return response()->json([
                'data' => $categories
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
