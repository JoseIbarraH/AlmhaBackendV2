<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Blog\Application\GetAllBlogsUseCase;
use Exception;

final class GetAllBlogsController
{
    private GetAllBlogsUseCase $useCase;

    public function __construct(GetAllBlogsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(): JsonResponse
    {
        try {
            $blogs = $this->useCase->execute();
            
            return response()->json([
                'data' => $blogs
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
