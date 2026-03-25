<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Blog\Application\DeleteBlogUseCase;
use Exception;

final class DeleteBlogController
{
    private DeleteBlogUseCase $useCase;

    public function __construct(DeleteBlogUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(int $id): JsonResponse
    {
        try {
            $this->useCase->execute($id);

            return response()->json([
                'message' => 'Blog deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
