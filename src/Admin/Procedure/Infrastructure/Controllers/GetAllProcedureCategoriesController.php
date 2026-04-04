<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query('page', '1');
            $perPage = (int) $request->query('per_page', '15');
            $categories = $this->useCase->execute($page, $perPage);
            
            return response()->json([
                'data' => $categories['items'],
                'meta' => $categories['meta']
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
