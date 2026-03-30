<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\DeleteProcedureUseCase;
use Exception;

use OpenApi\Attributes as OA;

final class DeleteProcedureController
{
    private DeleteProcedureUseCase $useCase;

    public function __construct(DeleteProcedureUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Delete(
        path: "/procedures/{id}",
        summary: "Eliminar un procedimiento",
        tags: ["Procedure"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del procedimiento",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Procedimiento eliminado exitosamente"
            ),
            new OA\Response(response: 404, description: "No encontrado"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(int $id): JsonResponse
    {
        try {
            $this->useCase->execute($id);
            return response()->json([
                'message' => 'Procedure deleted successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
