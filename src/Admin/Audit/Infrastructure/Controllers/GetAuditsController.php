<?php

declare(strict_types=1);

namespace Src\Admin\Audit\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
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
    public function __invoke(): JsonResponse
    {
        $audits = $this->useCase->execute();

        return response()->json([
            'data' => $audits
        ], 200);
    }
}
