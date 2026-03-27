<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\DeleteProcedureUseCase;
use Exception;

final class DeleteProcedureController
{
    private DeleteProcedureUseCase $useCase;

    public function __construct(DeleteProcedureUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(int $id): JsonResponse
    {
        try {
            $this->useCase->execute($id);
            return response()->json([
                'message' => 'Procedure deleted successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
