<?php

namespace Src\Admin\User\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Admin\User\Application\DeleteUserUseCase;
use Src\Admin\User\Domain\Exceptions\UserNotFoundException;
use Src\Shared\Infrastructure\Http\ApiResponse;

use OpenApi\Attributes as OA;

class DeleteUserController extends Controller
{
    private DeleteUserUseCase $useCase;

    public function __construct(DeleteUserUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Delete(
        path: "/users/{id}",
        summary: "Eliminar un usuario",
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
                description: "Usuario eliminado exitosamente"
            ),
            new OA\Response(response: 404, description: "Usuario no encontrado"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(string $id): JsonResponse
    {
        try {
            $this->useCase->execute($id);

            return ApiResponse::success(message: 'Usuario eliminado correctamente');
        } catch (UserNotFoundException $e) {
            return ApiResponse::error('not_found', $e->getMessage(), 404);
        }
    }
}
