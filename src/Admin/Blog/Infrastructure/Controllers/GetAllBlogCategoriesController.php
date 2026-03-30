<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Blog\Application\GetAllBlogCategoriesUseCase;
use Exception;

use OpenApi\Attributes as OA;

final class GetAllBlogCategoriesController
{
    private GetAllBlogCategoriesUseCase $useCase;

    public function __construct(GetAllBlogCategoriesUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Get(
        path: "/blog-categories",
        summary: "Listar todas las categorías de blog",
        tags: ["Blog"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de categorías",
                content: new OA\JsonContent(type: "array", items: new OA\Items(type: "object"))
            ),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(): JsonResponse
    {
        try {
            $categories = $this->useCase->execute();
            
            // Format models to arrays (the repository returns an array of models or aggregates depending on implementation)
            // Assuming the repo returns Eloquent models directly based on typical implementation, or array data.
            return response()->json([
                'data' => $categories
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
