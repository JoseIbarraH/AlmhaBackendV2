<?php

namespace Src\Admin\Role\Infrastructure\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Admin\Role\Application\CreateRoleUseCase;
use Src\Admin\Role\Domain\Exceptions\RoleAlreadyExistsException;

use OpenApi\Attributes as OA;

class CreateRoleController extends Controller
{
    private CreateRoleUseCase $useCase;

    public function __construct(CreateRoleUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    #[OA\Post(
        path: "/roles",
        summary: "Crear un nuevo rol",
        tags: ["Role"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "EDITOR")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Rol creado exitosamente"
            ),
            new OA\Response(response: 409, description: "El rol ya existe"),
            new OA\Response(response: 422, description: "Error de validación"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            $this->useCase->execute($request->input('name'));
            
            return response()->json([
                'message' => 'Rol creado correctamente'
            ], 201);
            
        } catch (RoleAlreadyExistsException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 409);
        }
    }
}
