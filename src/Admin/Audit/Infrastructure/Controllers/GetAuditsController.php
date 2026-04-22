<?php

declare(strict_types=1);

namespace Src\Admin\Audit\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Audit\Application\GetAuditsUseCase;
use Src\Shared\Infrastructure\Http\ApiResponse;
use Src\Shared\Infrastructure\Http\ValidatesPagination;
use OpenApi\Attributes as OA;

final class GetAuditsController
{
    use ValidatesPagination;

    private GetAuditsUseCase $useCase;

    public function __construct(GetAuditsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Get(
        path: "/audits",
        summary: "Obtener todos los registros de auditoría",
        tags: ["Audit"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de auditorías",
                content: new OA\JsonContent(type: "array", items: new OA\Items(type: "object"))
            ),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        [$page, $perPage] = $this->getPaginationParams($request);

        $audits = $this->useCase->execute($page, $perPage);

        return ApiResponse::paginated($audits);
    }
}
