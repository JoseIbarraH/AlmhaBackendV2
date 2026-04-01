<?php

namespace Src\Admin\Design\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Design\Application\DeleteDesignItemUseCase;

class DeleteDesignItemController
{
    private DeleteDesignItemUseCase $useCase;

    public function __construct(DeleteDesignItemUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(int $itemId): JsonResponse
    {
        $this->useCase->execute($itemId);

        return response()->json([
            'success' => true,
            'message' => 'Design item deleted successfully'
        ]);
    }
}
