<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\GetProcedureUseCase;
use Exception;

final class GetProcedureController
{
    private GetProcedureUseCase $useCase;

    public function __construct(GetProcedureUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(int $id): JsonResponse
    {
        try {
            $procedure = $this->useCase->execute($id);
            if (!$procedure) {
                return response()->json(['error' => 'Not found'], 404);
            }
            return response()->json($procedure);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
