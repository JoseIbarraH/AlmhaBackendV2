<?php

declare(strict_types=1);

namespace Src\Admin\Team\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Team\Application\DeleteTeamUseCase;

use OpenApi\Attributes as OA;

final class DeleteTeamController
{
    private DeleteTeamUseCase $useCase;

    public function __construct(DeleteTeamUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Delete(
        path: "/teams/{id}",
        summary: "Eliminar un miembro del equipo",
        tags: ["Team"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del miembro del equipo",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Miembro eliminado exitosamente"
            ),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(int $id): JsonResponse
    {
        $this->useCase->execute($id);

        return response()->json(['message' => 'Team member deleted successfully'], 200);
    }
}
