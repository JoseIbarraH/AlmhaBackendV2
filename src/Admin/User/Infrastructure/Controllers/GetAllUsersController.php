<?php

namespace Src\Admin\User\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Admin\User\Application\GetAllUsersUseCase;

use OpenApi\Attributes as OA;

class GetAllUsersController extends Controller
{
    private GetAllUsersUseCase $useCase;

    public function __construct(GetAllUsersUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Get(
        path: "/users",
        summary: "Listar todos los usuarios",
        tags: ["User"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de usuarios",
                content: new OA\JsonContent(type: "array", items: new OA\Items(type: "object"))
            ),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => $this->useCase->execute()
        ], 200);
    }
}
