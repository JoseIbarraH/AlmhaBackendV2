<?php

declare(strict_types=1);

namespace Src\Admin\Trash\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Trash\Application\GetTrashUseCase;

final class GetTrashController
{
    private GetTrashUseCase $useCase;

    public function __construct(GetTrashUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(): JsonResponse
    {
        $trash = $this->useCase->execute();

        return response()->json([
            'data' => $trash
        ]);
    }
}
