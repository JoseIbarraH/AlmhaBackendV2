<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Admin\Procedure\Application\GetProcedureUseCase;
use Exception;

use OpenApi\Attributes as OA;

final class GetProcedureController
{
    private GetProcedureUseCase $useCase;

    public function __construct(GetProcedureUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Get(
        path: "/procedures/{id}",
        summary: "Obtener detalle de un procedimiento",
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
                description: "Detalle del procedimiento",
                content: new OA\JsonContent(type: "object")
            ),
            new OA\Response(response: 404, description: "No encontrado"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(int $id, \Illuminate\Http\Request $request): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'es');
        try {
            $procedure = $this->useCase->execute($id, $lang);
            if (!$procedure) {
                return response()->json(['error' => 'Not found'], 404);
            }
            return response()->json($procedure);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
