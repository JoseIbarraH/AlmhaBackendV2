<?php

declare(strict_types=1);

namespace Src\Admin\Trash\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Trash\Application\GetTrashUseCase;

final class GetTrashController
{
    private GetTrashUseCase $useCase;

    public function __construct(GetTrashUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', '1');
        $perPage = (int) $request->query('per_page', '15');
        
        $trash = $this->useCase->execute($page, $perPage);

        return response()->json([
            'data' => $trash['items'],
            'meta' => $trash['meta'],
        ]);
    }
}
