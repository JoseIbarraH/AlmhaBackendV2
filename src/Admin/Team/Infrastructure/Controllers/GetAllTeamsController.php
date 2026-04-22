<?php

declare(strict_types=1);

namespace Src\Admin\Team\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Team\Application\GetAllTeamsUseCase;
use Src\Shared\Infrastructure\Http\ApiResponse;
use Src\Shared\Infrastructure\Http\ValidatesPagination;

use OpenApi\Attributes as OA;

final class GetAllTeamsController
{
    use ValidatesPagination;

    private GetAllTeamsUseCase $useCase;

    public function __construct(GetAllTeamsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Get(
        path: "/teams",
        summary: "Listar todos los miembros del equipo",
        tags: ["Team"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de miembros del equipo",
                content: new OA\JsonContent(type: "array", items: new OA\Items(type: "object"))
            ),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        [$page, $perPage] = $this->getPaginationParams($request);

        $result = $this->useCase->execute($page, $perPage);

        return ApiResponse::paginated($result);
    }
}
