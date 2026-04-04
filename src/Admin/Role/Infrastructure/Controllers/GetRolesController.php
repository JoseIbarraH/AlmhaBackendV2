<?php

namespace Src\Admin\Role\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Role\Application\GetAllRolesUseCase;

use OpenApi\Attributes as OA;

class GetRolesController extends Controller
{
    private GetAllRolesUseCase $useCase;

    public function __construct(GetAllRolesUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Get(
        path: "/roles",
        summary: "Listar todos los roles",
        tags: ["Role"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de roles",
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
