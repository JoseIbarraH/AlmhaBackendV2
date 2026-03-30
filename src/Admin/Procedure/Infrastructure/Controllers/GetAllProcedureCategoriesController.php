<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\GetAllProcedureCategoriesUseCase;
use Exception;

use OpenApi\Attributes as OA;

final class GetAllProcedureCategoriesController
{
    private GetAllProcedureCategoriesUseCase $useCase;

    public function __construct(GetAllProcedureCategoriesUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Get(
        path: "/procedure-categories",
        summary: "Listar todas las categorías de procedimientos",
        tags: ["Procedure"],
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
