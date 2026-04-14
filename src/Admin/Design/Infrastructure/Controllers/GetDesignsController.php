<?php

namespace Src\Admin\Design\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Design\Application\GetDesignsUseCase;

class GetDesignsController
{
    private GetDesignsUseCase $useCase;

    public function __construct(GetDesignsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $lang = substr($request->header('Accept-Language', 'es'), 0, 2);
        $designs = $this->useCase->execute($lang);

        return response()->json([
            'success' => true,
            'data' => $designs
        ]);
    }
}
