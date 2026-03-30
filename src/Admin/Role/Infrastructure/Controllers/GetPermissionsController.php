<?php

namespace Src\Admin\Role\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Admin\Role\Application\GetAllPermissionsUseCase;

use OpenApi\Attributes as OA;

class GetPermissionsController extends Controller
{
    private GetAllPermissionsUseCase $useCase;

    public function __construct(GetAllPermissionsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Get(
        path: "/permissions",
        summary: "Listar todos los permisos",
        tags: ["Role"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de permisos",
                content: new OA\JsonContent(type: "array", items: new OA\Items(type: "object"))
            ),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(): JsonResponse
    {
        $permissions = $this->useCase->execute();
        
        return response()->json([
            'data' => $permissions
        ], 200);
    }
}
