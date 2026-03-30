<?php

namespace Src\Admin\User\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\User\Application\UpdateUserUseCase;
use Src\Admin\User\Domain\Exceptions\UserNotFoundException;

use OpenApi\Attributes as OA;

class UpdateUserController extends Controller
{
    private UpdateUserUseCase $useCase;

    public function __construct(UpdateUserUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Post(
        path: "/users/{id}",
        summary: "Actualizar un usuario",
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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Jose Ibarra"),
                    new OA\Property(property: "email", type: "string", example: "jose@example.com"),
                    new OA\Property(property: "password", type: "string", example: "password123"),
                    new OA\Property(property: "is_active", type: "boolean", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Usuario actualizado exitosamente"
            ),
            new OA\Response(response: 404, description: "Usuario no encontrado"),
            new OA\Response(response: 422, description: "Error de validación"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'nullable|string|min:6',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $this->useCase->execute(
                $id,
                $request->input('name'),
                $request->input('email'),
                $request->input('password'),
                $request->input('is_active')
            );
            
            return response()->json([
                'message' => 'Usuario actualizado exitosamente'
            ], 200);
            
        } catch (UserNotFoundException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
