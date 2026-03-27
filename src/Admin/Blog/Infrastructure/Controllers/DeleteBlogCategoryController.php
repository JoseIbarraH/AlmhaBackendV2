<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Blog\Application\DeleteBlogCategoryUseCase;
use Exception;

final class DeleteBlogCategoryController
{
    private DeleteBlogCategoryUseCase $useCase;

    public function __construct(DeleteBlogCategoryUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request, int $id): JsonResponse
    {
        try {
            $this->useCase->execute($id);

            return response()->json([
                'message' => 'Blog category deleted successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
