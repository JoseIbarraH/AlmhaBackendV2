<?php

namespace Src\Admin\User\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\User\Application\GetAllUsersUseCase;
use Src\Shared\Infrastructure\Http\ApiResponse;
use Src\Shared\Infrastructure\Http\ValidatesPagination;

use OpenApi\Attributes as OA;

class GetAllUsersController extends Controller
{
    use ValidatesPagination;

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
    public function __invoke(Request $request): JsonResponse
    {
        [$page, $perPage] = $this->getPaginationParams($request);

        $result = $this->useCase->execute($page, $perPage);

        return ApiResponse::paginated($result);
    }
}
