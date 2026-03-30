<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\DeleteProcedureCategoryUseCase;
use Exception;

use OpenApi\Attributes as OA;

final class DeleteProcedureCategoryController
{
    private DeleteProcedureCategoryUseCase $useCase;

    public function __construct(DeleteProcedureCategoryUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Delete(
        path: "/procedure-categories/{id}",
        summary: "Eliminar una categoría de procedimientos",
        tags: ["Procedure"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la categoría",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Categoría eliminada exitosamente"
            ),
            new OA\Response(response: 404, description: "No encontrada"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request, int $id): JsonResponse
    {
        try {
            $this->useCase->execute($id);

            return response()->json([
                'message' => 'Procedure category deleted successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
