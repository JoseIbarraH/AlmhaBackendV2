<?php

declare(strict_types=1);

namespace Src\Admin\Team\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Team\Application\GetTeamUseCase;

use OpenApi\Attributes as OA;

final class GetTeamController
{
    private GetTeamUseCase $useCase;

    public function __construct(GetTeamUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Get(
        path: "/teams/{id}",
        summary: "Obtener detalle de un miembro del equipo",
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
                description: "Detalle del miembro",
                content: new OA\JsonContent(type: "object")
            ),
            new OA\Response(response: 404, description: "No encontrado"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(int $id): JsonResponse
    {
        $team = $this->useCase->execute($id);

        if (!$team) {
            return response()->json(['error' => 'Team member not found'], 404);
        }

        return response()->json($team, 200);
    }
}
