<?php

namespace Src\Admin\Role\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function __invoke(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', '1');
        $perPage = (int) $request->query('per_page', '15');

        $result = $this->useCase->execute($page, $perPage);
        
        return response()->json([
            'data' => $result['items'],
            'meta' => $result['meta']
        ], 200);
    }
}
