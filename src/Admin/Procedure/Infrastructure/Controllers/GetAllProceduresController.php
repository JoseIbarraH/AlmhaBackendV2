<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\GetAllProceduresUseCase;
use Exception;

use OpenApi\Attributes as OA;

final class GetAllProceduresController
{
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
                name: "lang",
                in: "query",
                required: false,
                description: "Idioma de los contenidos (es, en)",
                schema: new OA\Schema(type: "string", default: "es")
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
        $lang = $request->query('lang', 'es');
        try {
            $page = (int) $request->query('page', '1');
            $perPage = (int) $request->query('per_page', '15');
            $procedures = $this->useCase->execute($lang, $page, $perPage);
            return response()->json([
                'data' => $procedures['items'],
                'meta' => $procedures['meta']
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
