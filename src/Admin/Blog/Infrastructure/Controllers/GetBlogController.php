<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Blog\Application\GetBlogUseCase;
use Exception;

final class GetBlogController
{
    private GetBlogUseCase $useCase;

    public function __construct(GetBlogUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request, int $id): JsonResponse
    {
        try {
            $lang = $request->header('Accept-Language', 'es');
            $blog = $this->useCase->execute($id, $lang);
            
            return response()->json([
                'data' => $blog
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
