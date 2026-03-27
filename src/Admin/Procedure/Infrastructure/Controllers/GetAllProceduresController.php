<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\GetAllProceduresUseCase;
use Exception;

final class GetAllProceduresController
{
    private GetAllProceduresUseCase $useCase;

    public function __construct(GetAllProceduresUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $lang = $request->get('lang', 'es');
        try {
            $procedures = $this->useCase->execute($lang);
            return response()->json($procedures);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
