<?php

declare(strict_types=1);

namespace Src\Admin\Trash\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Trash\Application\DeleteTrashUseCase;

final class DeleteTrashController
{
    private DeleteTrashUseCase $useCase;

    public function __construct(DeleteTrashUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(string $type, string|int $id): JsonResponse
    {
        try {
            $this->useCase->execute($type, $id);

            return response()->json([
                'message' => 'Resource permanently deleted'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
