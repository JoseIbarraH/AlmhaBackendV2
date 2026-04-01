<?php

namespace Src\Admin\Design\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Design\Application\GetDesignsUseCase;

class GetDesignsController
{
    private GetDesignsUseCase $useCase;

    public function __construct(GetDesignsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(): JsonResponse
    {
        $designs = $this->useCase->execute();

        return response()->json([
            'success' => true,
            'data' => $designs
        ]);
    }
}
