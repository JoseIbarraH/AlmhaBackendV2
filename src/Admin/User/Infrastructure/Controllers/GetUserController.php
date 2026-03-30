<?php

namespace Src\Admin\User\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Admin\User\Application\GetUserUseCase;
use Src\Admin\User\Domain\Exceptions\UserNotFoundException;

use OpenApi\Attributes as OA;

class GetUserController extends Controller
{
    private GetUserUseCase $useCase;

    public function __construct(GetUserUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Get(
        path: "/users/{id}",
        summary: "Obtener detalle de un usuario",
        tags: ["User"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "UUID del usuario",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalle del usuario",
                content: new OA\JsonContent(type: "object")
            ),
            new OA\Response(response: 404, description: "Usuario no encontrado"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(string $id): JsonResponse
    {
        try {
            $user = $this->useCase->execute($id);

            return response()->json([
                'data' => [
                    'name' => $user->name()->value(),
                    'email' => $user->email()->value(),
                ]
            ], 200);
            
        } catch (UserNotFoundException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
