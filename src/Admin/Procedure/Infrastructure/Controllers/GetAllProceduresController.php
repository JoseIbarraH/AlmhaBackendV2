<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\GetAllProceduresUseCase;
use Src\Shared\Infrastructure\Http\ApiResponse;
use Src\Shared\Infrastructure\Http\ValidatesPagination;
use Exception;

use OpenApi\Attributes as OA;

final class GetAllProceduresController
{
    use ValidatesPagination;

    private GetAllProceduresUseCase $useCase;

    public function __construct(GetAllProceduresUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Get(
        path: "/procedures",
        summary: "Listar todos los procedimientos",
        tags: ["Procedure"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                schema: new OA\Schema(type: "string", default: "es")
            ),
            new OA\Parameter(
                name: "search",
                in: "query",
                required: false,
                description: "Buscar por título o subtítulo",
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "status",
                in: "query",
                required: false,
                description: "Filtrar por estado (published, draft)",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de procedimientos",
                content: new OA\JsonContent(type: "array", items: new OA\Items(type: "object"))
            ),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'es');
        try {
            [$page, $perPage] = $this->getPaginationParams($request);
            $search = $request->query('search');
            $status = $request->query('status');
            $procedures = $this->useCase->execute($lang, $page, $perPage, $search, $status);

            return ApiResponse::paginated($procedures);
        } catch (Exception $e) {
            return ApiResponse::error('server_error', $e->getMessage());
        }
    }
}
