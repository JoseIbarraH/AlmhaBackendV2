<?php

declare(strict_types=1);

namespace Src\Admin\Audit\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Audit\Application\GetAuditsUseCase;
use OpenApi\Attributes as OA;

final class GetAuditsController
{
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
        $page = (int) $request->query('page', '1');
        $perPage = (int) $request->query('per_page', '15');
        
        $audits = $this->useCase->execute($page, $perPage);

        return response()->json([
            'data' => $audits['items'],
            'meta' => $audits['meta'],
        ], 200);
    }
}
